# Kashida Branch Locator 2.5.31 — Release Notes

Release date: 2026-10-20

## New features

- Added an optional second phone number to branch records.
- Added an optional website URL for each branch.
- Displayed the website as a button instead of exposing a long raw URL in branch cards.
- Added an independent color setting for the Website button, alongside the Call and Directions button colors.
- Added Cards layout column-count control.
- Added an option to display branch cards grouped beneath their group headings.
- Added visitor-facing group selection with an “All Groups” option, alongside city filtering.

## Display Builder and administration

- Reworked the Display Builder workspace to provide more room for settings and the live preview.
- Improved the responsive layout and spacing of settings controls.
- Retained live preview and shortcode generation.
- Improved Arabic localization for admin navigation, branch/group actions, import screens, help text, frontend controls, action buttons, and result-count messages.

## Compatibility and maintenance

- Replaced unsupported `str_starts_with()` usage in the reported locations to retain compatibility with the declared WordPress 5.8 minimum.
- Synchronized the Stable Tag and plugin version metadata to 2.5.31.
- Updated repository documentation and changelog.

## Requirements

- WordPress 5.8 or later
- PHP 8.0 or later

## Installation

Download the attached `kashida-branch-locator-v2.5.31.zip` asset from the [GitHub Release](https://github.com/mrwan-bg/kashida-branch-locator/releases/tag/v2.5.31). Do not use GitHub's automatically generated “Source code” archive as the installable WordPress plugin ZIP.

## Validation status

The ZIP archive and PHP syntax were checked during preparation. Run WordPress Plugin Check and test installation, upgrade behavior, frontend layouts, and Arabic localization on a staging site before deploying to production.
