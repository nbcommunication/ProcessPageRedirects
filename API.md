# ProcessPageRedirects - API Documentation

## Overview

`ProcessPageRedirects` is a Process module that lists redirects that will
occur on the site, pulled from two sources:

1. **Site redirects** - old page paths recorded in ProcessWire's built-in
   `page_path_history` table (populated automatically by the core
   `PagePathHistory` module whenever a page is renamed or moved). These
   redirects are handled internally by ProcessWire's 404 handling.
2. **.htaccess redirects** - `RewriteRule` redirect lines parsed directly
   out of the site's `.htaccess` file.

The module renders both lists in a tabbed interface within the admin, with
a text filter field for narrowing results, and an option to export the
combined list to CSV.

## Properties

- `bool $hasLanguagePageNames`
  True when the multi-language page names field (`LanguageSupportPageNames`)
  is installed and active. When true, additional per-language edit links are
  rendered alongside each redirect row.

- `array $headers`
  Column headers used for both the on-screen markup tables and the CSV
  export, e.g. `array('Old URL', 'New URL', 'Modified')`.

- `array $redirectsSite`
  Rows sourced from the `page_path_history` table. Each row is an
  associative array with keys:
  - `path` (string) - the old page path
  - `pageID` (int) - the ID of the page the path used to belong to
  - `modified` (int) - unix timestamp the redirect was recorded

- `array $redirectsHtaccess`
  Rows parsed out of `.htaccess`. Each row is an associative array with
  keys:
  - `path` (string) - the redirect-from path/pattern
  - `redirectTo` (string) - the redirect-to destination

## Methods

### init()

Performs setup, including checking for the multi-language page names
module and verifying the site's `.htaccess` file exists before attempting
to read and parse it. If the file does not exist, `.htaccess` parsing is
skipped rather than triggering a warning.

### execute()

Renders the module's tabbed interface:

- **Site** tab - lists redirects sourced from `$redirectsSite`, with a link
  to edit the page for each row.
- **.htaccess** tab - lists redirects sourced from `$redirectsHtaccess`.

Both tabs include a text filter input (client-side JS filtering of table
rows) and a button to trigger CSV export via `executeExport()`.

All redirect path/destination values rendered into the markup are passed
through `$sanitizer->entities()` before being placed in HTML attributes or
table cells, to prevent any historical page path (which could contain
arbitrary characters) from resulting in HTML/attribute injection.

### executeExport()

Handles the CSV export request (triggered from either tab). Combines
`$redirectsSite` and `$redirectsHtaccess` into a single CSV using
`$headers` as the header row, then sends it as a file download with:

- A single `Content-Type: application/octet-stream` header
- A `Content-Disposition: attachment; filename="..."` header, with the
  filename quoted and a `Ymd-His` timestamp appended
- A correctly formatted RFC 7231 `Last-Modified` header using
  `gmdate('D, d M Y H:i:s') . ' GMT'`

Execution ends with `die()` after headers and CSV body are sent, so this
method should not be called from automated test contexts.

## Requirements

- ProcessWire 3.0.x
- PHP 7.4+

## Permissions

- `page-edit-redirects` - required to access this Process module from the
  admin. Superusers always have access regardless of assigned permissions.

## Changelog

### 205
- Fixed missing HTML-entity escaping on redirect path/destination output
  (potential HTML/attribute injection from historical page path data).
- Added file-existence check before reading `.htaccess`, avoiding a
  warning/deprecation notice when the file is absent.
- Fixed malformed `Last-Modified` response header (now RFC 7231 compliant
  GMT-formatted date).
- Removed redundant duplicate `Content-Type` headers on CSV export.
- Quoted CSV export filename and switched timestamp format to `Ymd-His`.