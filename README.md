# ProcessPageRedirects

Lists site redirects in the ProcessWire admin, gathered from two sources:

- **Site** - page path history recorded by the core [PagePathHistory](https://processwire.com/modules/page-path-history/) module (populated automatically when pages are renamed or moved).
- **htaccess** - `RewriteRule` redirects parsed from the site's `.htaccess` file.

Click on a `Redirect Path` to test the redirect in a new tab.

On the Site tab, click on a `Destination` to edit that page (scroll down to 'What other URLs redirect to this page?').

Each tab includes a filter field to quickly search redirects, and a `Download CSV` button to export the list shown in that tab.

## Requirements

**ProcessWire >= 3.0.225 and PHP >= 8.1 are required to use this module.**

The Site tab requires the core [PagePathHistory](https://processwire.com/modules/page-path-history/) module to be installed. If it is not installed, the Site tab will simply show no redirects.

## Installation

[Install](https://processwire.com/docs/modules/install/) the ProcessPageRedirects module.

A new page is added to the admin: **Pages > Redirects**.

## Permissions

This module uses the `page-edit-redirects` permission. Users must have this permission (in addition to admin access) to view the Redirects page.

## Usage

- Open **Pages > Redirects** in the admin.
- Use the **Site** tab to view redirects generated automatically from page renames/moves.
- Use the **htaccess** tab to view redirects manually defined in `.htaccess`.
- Type in the **Filter Redirects** field on either tab to narrow down the list.
- Click **Download CSV** to export the current tab's redirects.

## Changelog

### 205

- Fixed unescaped/unquoted redirect path and destination links, which could allow HTML/attribute injection from page path history or `.htaccess` data.
- Added a file-existence check before reading `.htaccess`, avoiding a warning (and PHP 8.1+ deprecation) when the file is missing.
- Fixed a malformed `Last-Modified` export header so it is properly RFC 7231 compliant GMT format.
- Removed redundant duplicate `Content-Type` headers on CSV export.
- Quoted the CSV export filename and standardised its timestamp format.
