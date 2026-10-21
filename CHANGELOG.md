# Changelog

## 2.5.32

- Added readable JavaScript source copies for frontend, admin, and import scripts.
- Documented source file locations and maintenance guidance in the plugin readme.
- Lowered the top-level admin menu position from 25 to 80.
- Refined the Import and Settings admin page layouts.
- Updated plugin version metadata and release documentation.


## Unreleased

- Added selection persistence across admin pages for branches and groups.
- Added the option to select all branches matching the current search and city/group filters across every page.
- Updated the plugin header description and readme feature documentation.
- Kept the release version at 2.5.31; these changes are prepared for review before merging.
- Added a configurable default color for newly created groups.
- Added synchronized HEX entry alongside the group color picker.
- Added a small settings page for the default group color; existing group colors remain unchanged.

All notable releases of Kashida Branch Locator are listed here. For full historical details, see the version history in [`kashida-branch-locator/readme.txt`](kashida-branch-locator/readme.txt) and the [GitHub Releases](https://github.com/mrwan-bg/kashida-branch-locator/releases) page.

## [2.5.31] - 2026-10-20

### Added
- Second phone number field for branches.
- Website URL field for branches, displayed as a configurable website button rather than a long raw URL.
- Independent color settings for Call, Directions, and Website buttons.
- Display Builder options for Cards column count and grouping branch cards beneath their group headings.
- Group and city filters for visitor-facing branch directories, including an all-groups option.
- Expanded Arabic localization coverage for admin screens, frontend labels, buttons, result counts, and help content.

### Improved
- Responsive Display Builder layout with a wider, better-organized settings workspace and live preview.
- Branch and group management screens and branch-card presentation.
- Arabic labels and fallback strings for frontend UI text, including “Showing %1$d of %2$d results.”
- WordPress 5.8 compatibility by avoiding `str_starts_with()` where unsupported.
- Version metadata and Stable Tag alignment.

### Compatibility and release notes
- Minimum WordPress version remains 5.8.
- Minimum PHP version remains 8.0.
- The published ZIP is the installable plugin package; GitHub-generated source archives are not WordPress plugin ZIPs.

## [2.5.22]

- GitHub distribution package prepared from the supplied plugin release archive.
- Added repository documentation, screenshots, contribution guidance, issue templates, and PHP syntax workflow.

> Note: These are repository packaging changes, not a claim of new plugin runtime features.
