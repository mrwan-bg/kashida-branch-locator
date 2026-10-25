## Database compatibility follow-up (plugin version remains 2.5.33)
- Use TIMESTAMP for created_at defaults to improve compatibility with older supported MySQL versions.
- Only save the schema version after verifying both tables and required columns.

## 2.5.33
- Updated plugin version metadata; no feature changes.

# Changelog

## 2.5.32

- Added readable JavaScript source files for frontend, admin, and import scripts, with documentation in `readme.txt`.
- Moved the top-level admin menu to a lower-priority position.
- Refined the Import and Settings admin page layouts.

## Previous changes

- Added a Branch Display Mode setting with Pagination and Infinite Scroll options.
- Added cross-page selection persistence for branches and groups in the admin interface.
- Added an option to select all branches matching the current search and filters, across all admin pages.
- Added a configurable default color for new groups and a synchronized HEX color input.
- Fixed frontend phone links so multiple numbers are separate links and the Call button uses the primary number.
- Improved language-aware frontend direction and English text alignment while preserving Arabic RTL behavior.
- Updated plugin and readme descriptions to reflect current branch management, import, filtering, display, and map features.
- Kept plugin metadata and the Stable Tag synchronized at version 2.5.31 in the previous release.

