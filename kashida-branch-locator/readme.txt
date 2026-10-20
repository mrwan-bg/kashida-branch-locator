=== Kashida Branch Locator ===
Contributors: mrwanbg
Tags: branches, locations, map, store locator, directory
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 2.5.31
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight branch and location directory for WordPress with groups, CSV/XLSX import, responsive layouts, and interactive OpenStreetMap maps.

== Description ==

Kashida Branch Locator provides a simple way to manage company branches, offices, stores, service centers, or other locations and display them on WordPress pages using shortcodes.

The plugin is designed to remain lightweight and does not require a page builder or a separate settings framework.

= Features =

* Branch management from the WordPress dashboard.
* Optional groups with custom colors.
* Display Builder controls for Cards column count and displaying branch cards under group headings.
* City and group filtering, including an all-groups option where enabled.
* Configurable Call, Directions, and Website buttons with independent colors.
* Live search, city and group filters, sortable columns, result counts, reset controls, and pagination.
* Table, cards, list, accordion, map, and map-table layouts.
* CSV and XLSX import with preview and optional update of existing branches.
* Responsive frontend output.
* Frontend typography and direction inherited from the active theme.
* Interactive maps using OpenStreetMap tiles without loading third-party JavaScript or CSS libraries. Filtered map markers stay synchronized with table results, and selecting a marker highlights its matching branch.
* Google Maps directions link for each branch.
* Optional second phone number and website URL for each branch; website URLs are presented through a configurable button.
* RTL support and mobile-friendly table cards.
* Nonce and capability checks for administrative AJAX actions.
* Coordinate and group validation.

== Shortcodes ==

`[kashida_branch_locator]`

`[kashida_branch_locator layout="table"]`

`[kashida_branch_locator layout="cards"]`

`[kashida_branch_locator layout="accordion"]`

`[kashida_branch_locator layout="list"]`

`[kashida_branch_locator layout="map"]`

`[kashida_branch_locator layout="map-table" search="true" pagination="true"]`

`[kashida_branch_locator group="grp_xxxxxxxx"]`

`[kashida_branch_locator city="Tripoli"]`

`[kashida_branch_locator branch="br_xxxxxxxx"]`

`[kashida_branch_locator groups="grp_xxxxxxxx,grp_yyyyyyyy" layout="cards"]`

`[kashida_branch_locator branches="br_xxxxxxxx,br_yyyyyyyy" layout="map-table"]`

`[kashida_branch_locator layout="map" group="grp_xxxxxxxx" zoom="6" height="600"]`

`[kashida_branch_group_map group="grp_xxxxxxxx" zoom="6" height="600"]`

== Import ==

CSV and XLSX files are supported directly.

Required columns:

* Name
* City
* Address
* Phone
* Lat
* Lng
* Group

Latitude and longitude may be provided as separate `Lat` and `Lng` columns. The legacy `Location` value in `latitude,longitude` format is also supported.

Legacy `.xls` files require PhpSpreadsheet to be available on the server. CSV or XLSX is recommended.

The maximum upload size accepted by the plugin is 10 MB.

== External Services ==

This plugin uses the following external services only when the related frontend functionality is used:

= OpenStreetMap =

Map tiles are loaded from OpenStreetMap's tile service to display the interactive map. A visitor's browser connects to the OpenStreetMap tile server and therefore the visitor's IP address and normal HTTP request information may be processed by that service.

Service: https://www.openstreetmap.org/
Tile service: https://tile.openstreetmap.org/
Copyright and tile usage information: https://www.openstreetmap.org/copyright
Privacy information: https://osmfoundation.org/wiki/Privacy_Policy

= Google Maps =

The plugin provides a user-initiated Directions link that opens Google Maps in a new browser tab. The plugin does not send branch data to Google automatically; the browser opens the Google Maps URL only after the visitor selects the link.

Service: https://www.google.com/maps/
Google privacy policy: https://policies.google.com/privacy
Google Maps terms: https://www.google.com/help/terms_maps/

== Privacy ==

Kashida Branch Locator does not include analytics, advertising, telemetry, licensing checks, or automatic tracking. It does not contact the developer's servers for plugin operation.

When maps are displayed, the visitor's browser requests map tiles from OpenStreetMap. When a visitor chooses Directions, the browser opens Google Maps. Site owners should review the privacy policies and terms of those services for their own sites.

== Installation ==

1. Upload the `kashida-branch-locator` folder to `/wp-content/plugins/`, or install the plugin ZIP from the WordPress Plugins screen.
2. Activate Kashida Branch Locator from the Plugins screen.
3. Open Kashida Branch Locator in the WordPress dashboard.
4. Add groups and branches, or import branches from CSV/XLSX.
5. Add one of the Kashida Branch Locator shortcodes to a page.

== Frequently Asked Questions ==

= Does the plugin require a map API key? =

No. The map uses OpenStreetMap tiles and does not require a Google Maps API key.

= Does the plugin force a font? =

No. The frontend inherits the active theme's typography and text direction.

= Can I use the plugin without a map? =

Yes. Use table, cards, list, or accordion layouts. The map assets and map functionality are only initialized for map layouts.

= Can I import branches from Excel? =

Yes. XLSX is supported directly. Legacy XLS files require PhpSpreadsheet on the server.

= Does the plugin track visitors? =

No. The plugin does not include analytics or tracking. OpenStreetMap and Google Maps are external services and are contacted only for the documented map functionality.

== 2.5.31 ==
* Added a second phone number and website URL to branch records and forms.
* Added a configurable website button instead of displaying a long raw website URL on branch cards.
* Added independent button color controls for Call, Directions, and Website.
* Improved the Display Builder layout and responsive settings/preview arrangement.
* Added Cards column-count controls and group headings for grouped branch cards.
* Improved city and group filtering controls, including the all-groups option.
* Expanded Arabic translations for administration screens, buttons, frontend labels, results count, and help content.
* Improved WordPress 5.8 compatibility by replacing unsupported string-prefix helpers.
* Synchronized release version metadata and Stable Tag.

== 2.5.16 ==
* Strengthened plugin-specific namespaces and WordPress identifiers to reduce naming collisions.
* Added migration support for existing branch and group tables and database version option.
* Replaced the legacy unprefixed `[branch_group_map]` shortcode with `[kashida_branch_group_map]` to reduce naming collisions. Update existing pages that use the legacy shortcode.
* Updated contributor metadata and prepared translation assets for WordPress.org translation management.

== 2.2.1 ==
* Added a unified Kashida Media Services footer to all plugin admin pages with the Kashida logo and creator links.

== 2.2.0 ==
* Added live Display Builder preview.
* Improved import validation, row-level warnings, and downloadable error reports.
* Added privacy-policy guidance for stored branch data and documented external map services.
* Improved map accessibility and handling of branches without valid coordinates.
* Added troubleshooting documentation and refined admin UX.

== 2.0.8 ==
* Updated the import template to contain column instructions only; no sample locations.

== 2.5.4 ==
* Compact table row spacing and safer action-column sizing in RTL/LTR.
* Fixed table content overflow and action buttons clipping.
* Tightened list layout spacing and alignment.

== Changelog ==

= 2.5.18 =
* Added automatic database schema upgrade checks for plugin updates.
* Improved CSV/XLSX header matching for case, spacing, underscores, and BOM variations.
* Hardened spreadsheet row handling to skip blank rows and safely handle differing row widths.
* Reset import diagnostics for each import run to prevent stale counts or messages.
* Documented intentional direct database access for custom-table operations.



= 2.5.17 =
* Fixed import reporting so failed database writes are no longer counted as successful.
* Added clear errors when plugin database tables are missing or an uploaded workbook contains no data rows.
* Fixed updates being treated as failures when the record exists but values are unchanged.


= 2.5.16 =
* Suppress justified Plugin Check warnings for custom-table direct database operations.
* Use WordPress-supported array arguments for prepared branch queries and document the custom-table exception.
* Clarify template-scope variable handling for the archive template.

= 2.5.14 =
* Fix direct file access protection for the single branch template.
* Use WordPress-compatible prepared SQL argument expansion in branch queries.
* Prefix template variables to satisfy global naming checks.


= 2.5.13 =
* Improved WordPress.org Plugin Check compatibility.
* Updated readme tested version format.
* Added direct-access protection to template files.
* Replaced PHP 8-only string helper usage with WordPress 5.8-compatible logic.
* Removed the discouraged manual translation loading call for WordPress.org-hosted translations.
* Improved admin input handling and Plugin Check compatibility for AJAX requests.
* Improved prepared SQL argument handling for branch queries.

* Added required translators comments for all localized strings containing placeholders.
* Improved translation-source compatibility with WordPress Plugin Check.

= 2.5.9 =
* Completed Arabic localization coverage for current admin UI strings, including branch/group headers and import instructions.
* Added missing translatable strings to the POT/PO catalogs and refreshed the Arabic MO catalog.


= 2.5.8 =
* Fixed search control visibility in Map + Table layout.

= 2.5.7 =
* Renamed the Arabic plugin/admin label to "مواقع الفروع كشيدة".
* Replaced the dashboard header logo with the Kashida mark.

= 2.5.6 =
* Added a clear visual gap between Call and Directions action buttons in table layouts.

= 2.5.5 =
* Fixed table action column width and prevented Call/Directions labels from wrapping vertically.
* Reduced table row spacing and improved compact responsive table cards on small screens.

= 2.5.3 =
* Redesigned Map + Table presentation with the map above the table on desktop and mobile.
* Removed horizontal scrolling from Map + Table tables and improved responsive table cards on small screens.
* Refined the plugin admin screens with a unified modern visual system, clearer headers, toolbars, tables, forms, and responsive spacing.



= 2.5.2 =
* Improved Map + Table spacing and mobile stacking.
* Fixed mobile Map + Table table overflow and clipping.
* Improved List layout spacing and mobile responsiveness.
* Removed remaining decorative media spacing from Cards and List layouts.

= 2.5.1 =
* Refined Cards and List layouts by removing decorative image/media placeholder areas and tightening the content layout.

= 2.5.0 =
* Major UI refresh for admin Display Builder and frontend layouts.
* Redesigned Table, Cards, List, Accordion, Map, and Map + Table presentation.
* Map + Table now uses a connected split view with synchronized results and map.
* Improved frontend actions, responsive spacing, controls, and visual hierarchy.


= 2.4.0 =
* Final frontend polish: map/table synchronization, marker-to-row selection, row-to-marker selection, and mobile map touch guidance.
* Refined RTL/LTR behavior and responsive table/card/list presentation.
* Updated Help documentation for visitor controls, search, sorting, map interaction, layouts, mobile behavior, and performance guidance.
* Final pre-release static review and validation pass.
= 2.3.6 =
* Refined Cards and List presentation with neutral containers, improved spacing, and clearer visual hierarchy.
* Removed strong group-color edge accents from Cards and List; group colors remain available through badges.
* Added consistent internal spacing so labels, values, and actions no longer sit against container edges.


= 2.3.4 =
* Added a separate Display Builder option to show or hide the visitor sorting control while keeping sortable table headers available.

= 2.3.3 =
* Enabled desktop mouse map panning and protected mobile map interaction by requiring a two-finger gesture for map movement.
* Added a mobile touch hint and improved map drag feedback.


= 2.3.0 =
* Improved frontend search, filtering, sorting, result counts, reset controls, and pagination feedback.
* Synchronized filtered table results with map markers and marker-to-row selection.
* Improved mobile table presentation and accessibility labels.
* Added Arabic localization for new visitor controls.


= 2.2.4 =
* Restored sortable table headers and improved frontend search/sort controls for a cleaner, clearer visitor experience.

= 2.2.2 =
* Improved visitor sorting UI with clear sort indicators on sortable table headers.
* Added a visible hint explaining that City and Group headers can be clicked to sort.

= 2.0.7 =
* Display group names with their configured group color in frontend badges.
* Added a downloadable XLSX sample import template to Help.


= 2.0.6 =
* Refined admin page branding, tables, selection toolbar, and responsive controls.
* Added official Kashida logo asset to the admin interface.

= 2.0.4 =
* Added bulk selection for branches and groups.
* Added bulk deletion for selected branches and groups.
* Added combined shortcodes using `groups="..."` and `branches="..."`.
* Improved admin table styling and selection controls.
* Expanded Arabic translations and rebuilt the Arabic MO file.
* Updated Help documentation for bulk selection and combined displays.


= 2.0.2 =
* Improved admin Help with a guided Display Builder workflow and cleaner documentation.
* Improved map popup layering so the active location card stays above all markers.
* Added copy buttons to Help shortcode examples.


= 2.0.1 =
* Rebuilt frontend display engine from scratch.
* All display layouts now use one reliable client-side rendering path.
* Map and Map + Table use embedded branch data and visible map markers.
* Display Builder generates the Kashida Branch Locator shortcode.
* Shortcode is `[kashida_branch_locator]`.



= 1.7.6 =
* Fixed map + table rendering and marker visibility.
* Improved marker positioning and popup handling.
* Improved Display Builder shortcode generation for selected fields.


= 1.7.7 =
* Improved map markers and marker-to-table selection.
* Added reliable coordinate validation and single-location map centering.


= 1.7.0 =
* Updated plugin headers and licensing information for WordPress.org distribution.
* Added a WordPress.org-compatible readme with external service and privacy documentation.
* Removed third-party CDN JavaScript and CSS dependencies.
* Replaced the Leaflet/MarkerCluster frontend dependency with a lightweight native map renderer using OpenStreetMap tiles.
* Expanded Arabic translations and updated the POT/PO/MO translation files.
* Localized import-interface messages.
* Removed the remote Kashida logo from the Help page and kept the branding local to the plugin.
* Improved WordPress theme integration and frontend isolation.
* Fixed an administrative branch-save error response.
* Retained nonce, capability, upload, coordinate, and group validation.

= 1.6.0 =
* Cleaner WordPress admin styling.
* Theme typography inheritance on the frontend.
* Improved RTL/LTR behavior and responsive controls.
* Improved Help page and shortcode documentation.
* Fixed admin group filtering.
* Added secure upload validation for CSV/XLSX imports.
* Fixed the import AJAX flow.
* Added coordinate and group validation.
* Limited public map responses to 1,000 branches.
* Safer native XLSX XML parsing.

== Credits ==

Kashida Branch Locator is designed and developed by Kashida Media Services.

https://kashida.ly/
