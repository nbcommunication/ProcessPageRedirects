<?php namespace ProcessWire;

/**
 * WireTest for ProcessPageRedirects
 *
 * Exercises the module's public API: execute() and the redirects it builds
 * from page_path_history during init(), including the entity-escaping fix
 * applied to redirect paths rendered in the admin table.
 *
 * Deliberately NOT covered:
 * - executeExport(), since it calls die() after sending download headers -
 *   not safe to invoke from an automated test process. Verify manually via
 *   the "Download CSV" button on each tab.
 * - The .htaccess RewriteRule parsing branch of init(), since exercising it
 *   properly would require writing to the live site .htaccess file. Verify
 *   manually against a real redirect rule.
 *
 */
class WireTest_ProcessPageRedirects extends WireTest {

	/**
	 * Pages created during a test, so they can be cleaned up even on failure
	 *
	 * @var array
	 *
	 */
	protected $cleanupPageIds = [];

	/**
	 * Only run if the module is actually installed
	 *
	 * @return bool
	 *
	 */
	public function allow() {
		return $this->wire()->modules->isInstalled('ProcessPageRedirects')
			&& $this->wire()->modules->isInstalled('PagePathHistory');
	}

	/**
	 * Get a fresh ProcessPageRedirects instance with init() already run
	 *
	 * The module is singular, so modules->get() would return the cached
	 * instance from boot time, which won't reflect page_path_history rows
	 * created during this test. Construct + init() a new instance instead.
	 *
	 * @return ProcessPageRedirects
	 *
	 */
	protected function newProcessPageRedirects() {
		$m = new ProcessPageRedirects();
		$this->wire($m);
		$m->init();
		return $m;
	}

	/**
	 * A parent page to create temporary test pages under
	 *
	 * @return Page
	 *
	 */
	protected function getTestParent() {
		$parent = $this->wire()->pages->get('/wire-test/');
		if(!$parent->id) $parent = $this->wire()->pages->get(1); // fallback to home
		return $parent;
	}

	public function execute() {
		try {
			$this->testExecuteReturnsFormWithTabs();
			$this->testRedirectsSiteFromPagePathHistory();
			$this->testRedirectEscaping();
		} finally {
			$this->cleanup();
		}
	}

	/**
	 * Remove any pages/database rows created during the test
	 *
	 */
	protected function cleanup() {
		$database = $this->wire()->database;
		foreach($this->cleanupPageIds as $id) {
			$database->query("DELETE FROM page_path_history WHERE pages_id=" . (int) $id);
			$page = $this->wire()->pages->get((int) $id);
			if($page->id) {
				$page->delete();
			}
		}
		$this->cleanupPageIds = [];
	}

	protected function testExecuteReturnsFormWithTabs() {

		$m = $this->newProcessPageRedirects();
		$out = $m->execute();

		$this->check('execute() returns a non-empty string', true, is_string($out) && strlen($out) > 0);
		$this->check('execute() output includes the Site tab', true, strpos($out, 'Site (') !== false);
		$this->check('execute() output includes the htaccess tab', true, strpos($out, 'htaccess (') !== false);
	}

	protected function testRedirectsSiteFromPagePathHistory() {

		$pages = $this->wire()->pages;
		$parent = $this->getTestParent();

		$p = $this->wire(new Page());
		$p->template = 'default';
		$p->parent = $parent;
		$p->title = 'WireTest ProcessPageRedirects Temp';
		$p->name = 'wiretest-ppr-temp-orig-' . time();
		$p->save();
		$this->cleanupPageIds[] = $p->id;

		$oldPath = $p->path;

		// Renaming records a page_path_history row automatically via PagePathHistory
		$p->name = 'wiretest-ppr-temp-renamed-' . time();
		$p->save();

		$m = $this->newProcessPageRedirects();
		$out = $m->execute();

		$oldPathEscaped = $this->wire()->sanitizer->entities($oldPath);

		$this->check(
			'execute() output includes the old (pre-rename) path from page_path_history',
			true,
			strpos($out, $oldPathEscaped) !== false
		);
		$this->check(
			'execute() output includes a link to the page\'s current edit URL',
			true,
			strpos($out, $p->editURL) !== false
		);
	}

	protected function testRedirectEscaping() {

		$pages = $this->wire()->pages;
		$database = $this->wire()->database;
		$parent = $this->getTestParent();

		$p = $this->wire(new Page());
		$p->template = 'default';
		$p->parent = $parent;
		$p->title = 'WireTest ProcessPageRedirects XSS Temp';
		$p->name = 'wiretest-ppr-xss-temp-' . time();
		$p->save();
		$this->cleanupPageIds[] = $p->id;

		// Manually insert a crafted history row - simulates any historic/legacy
		// data containing characters a normal page rename wouldn't produce, to
		// confirm the module's output escaping holds regardless of data source.
		$maliciousPath = '/"><script>alert(1)</script>';
		$stmt = $database->prepare("INSERT INTO page_path_history (path, pages_id, language_id, created) VALUES (:path, :pages_id, 0, NOW())");
		$stmt->execute([':path' => $maliciousPath, ':pages_id' => $p->id]);

		$m = $this->newProcessPageRedirects();
		$out = $m->execute();

		$this->check(
			'execute() does not output an unescaped script tag from a crafted redirect path',
			false,
			strpos($out, '<script>alert(1)</script>') !== false
		);
		$this->check(
			'execute() entity-encodes double quotes in the crafted redirect path',
			true,
			strpos($out, '&quot;') !== false
		);
	}
}