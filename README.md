# ProcessPageRedirects

Provides a interface for viewing and managing redirects for all page editors.

**ProcessWire >= 3.0.165 and PHP >= 7.3 are required to use this module.**

## Page Redirects

The overview page can be accessed from Pages > Redirects. This displays a table listing all the pages on the site that are viewable by the current user.

A number of actions can be taken from the table. Clicking on;
* the `title` will open the page editor in a new tab/window
* the `url` will open the page in a new tab/window
* the number of redirects will open the redirects editor, if the page is editable by the current user

## Edit Redirects

The redirects editor is the same one that can be accessed from the 'Settings' tab while editing a page, but is available to any user with `page-edit` permission for the page.
