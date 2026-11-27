# Changelog

## 2.7.0

- Refined all six frontend layouts with softer borders, clearer contrast, balanced spacing, and a consistent visual hierarchy.
- Changed group color presentation to colored text rather than large filled badges or rectangles.
- Improved compact mobile table cards with distinct boundaries and clearer branch information.
- Standardized search and filter control heights; changed Search to a neutral gray style and reduced result-count text size.
- Improved Display Builder width, preview clarity, and settings organization.
- Refined admin navigation, settings controls, forms, and modal presentation.
- Improved branch import and Display Builder preview behavior.
- Improved SQL preparation, output escaping, and Plugin Check compatibility.
- Tested successfully with WordPress 7.2 and PHP 8.6; passed WordPress Plugin Check.

- Admin refresh: pill navigation across all plugin pages, refined headers, tables, forms and modals that follow the WordPress admin color scheme.
- Settings: new Button Colors section (call, directions, details, website) with live preview; empty values follow the theme.
- Settings: new Display Container section (maximum width with slider and presets, alignment) and Theme Integration toggle.
- Shortcode: new attributes width, theme_colors, details_color; call/directions/website colors now fall back to the Settings values.
- Single branch card now uses the website button color; Arabic translations added for all new strings.
- Hardened theme-color detection against unexpected theme data.

## 2.6.2

- Single branch view is now a card with icons and Call / Directions / Website buttons.
- Theme adaptation of accent color, button text color and radius (theme.json, theme mods, editor palette). Filter: kashida_bl_theme_colors.

## 2.6.1

- Redesigned all six layouts (table, cards, list, accordion, map, map + table) with SVG icons, search toolbar, and details popup.
- Map: group-coloured pins with a store icon, group legend with show/hide, locate-me button, larger theme-relative popup buttons.
- Map and Map + Table layouts show the map on top and the full branch table below.
- All frontend typography now follows the active theme font family and size (em-based).

## 2.5.35

- Extended active-theme font-family inheritance to all frontend descendants, including map popup content and action links.
- Removed reduced fixed font sizes from group badges and frontend action buttons so they follow the theme typography scale.
- Improved map popup action button sizing while preserving the existing colors and behavior.

## 2.5.34

- Fixed group updates so unchanged values are treated as successful saves while database failures are reported accurately.
- Improved synchronization between the WordPress color picker and the HEX field when editing groups.
- Reworked Display Builder previews so visible fields and options are reflected in the selected layout.
- Improved Display Builder sizing, responsive behavior, and overflow handling.
- Updated frontend controls to inherit typography from the active theme.


## 2.5.33

- Added a button on the plugin Help page linking to the Kashida Branch Locator page on the Kashida website.
- Kept the release metadata aligned at version 2.5.33.
- Expanded the Settings page form and footer to use the available WordPress admin content width.
- Removed a hidden SonarCloud configuration file that triggered the Plugin Check hidden-files error.

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

