# JavaScript source files

This directory contains readable source copies of the JavaScript shipped with Kashida Branch Locator. These files are included for review, study, and maintenance.

## Source-to-production mapping

- `js/frontend.js` corresponds to `../../js/frontend.js`.
- `js/admin.js` corresponds to `../../js/admin.js`.
- `js/import.js` corresponds to `../../js/import.js`.

The production files in `assets/js/` are enqueued by WordPress. The files in `assets/src/js/` are not enqueued. The source copies preserve the production code while adding line breaks at statement boundaries for readability; they are not a separate implementation, and no build step is required to install the plugin.

When changing JavaScript, keep the readable source and production asset synchronized, then run syntax checks and test the related plugin workflows before release.
