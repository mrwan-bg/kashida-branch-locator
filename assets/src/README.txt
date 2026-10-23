Kashida Branch Locator source files
===================================

These readable files are included so reviewers and developers can inspect the code.

JavaScript source files:
- js/frontend.js corresponds to ../js/frontend.js
- js/admin.js corresponds to ../js/admin.js
- js/import.js corresponds to ../js/import.js

CSS source files:
- css/admin.css corresponds to ../css/admin.css
- css/frontend.css corresponds to ../css/frontend.css

The production files in assets/js/ and assets/css/ are loaded by WordPress. Files in assets/src/ are not enqueued. Keep source and production behavior synchronized whenever either side is changed. No external build tool is required to install the plugin.
