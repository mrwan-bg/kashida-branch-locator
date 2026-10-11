<p align="center">
  <img src="assets/banner-772x250.png" alt="Kashida Branch Locator" width="772">
</p>

# Kashida Branch Locator

A lightweight WordPress branch and location directory with groups, CSV/XLSX import, responsive layouts, and interactive OpenStreetMap maps.

<p align="center">
  <img src="assets/icon-256x256.png" alt="Kashida Branch Locator icon" width="128">
</p>

**Current release:** `v2.5.22`  
**Developer:** Kashida Media Services — Mrwan elGobee  
**License:** GPL-2.0-or-later

## Features

- Manage branches and locations from the WordPress dashboard.
- Organize locations into optional groups with custom colors.
- Display locations as tables, cards, lists, accordions, maps, or map-and-table layouts.
- Search, filter by city/group, sort table columns, and paginate results.
- Import CSV and XLSX files with preview and optional updates to existing branches.
- Responsive frontend layouts, RTL support, and theme typography inheritance.
- Interactive maps using OpenStreetMap tiles; marker selection stays synchronized with visible results.
- User-initiated Google Maps directions links.
- Administrative nonce and capability checks, plus input and coordinate validation.

## Screenshots

### Branch management

![Branch management](assets/screenshot-1.png)

### Group management

![Group management](assets/screenshot-2.png)

### Import workflow

![Import interface](assets/screenshot-3.png)

### Frontend display

![Frontend display](assets/screenshot-4.png)

## Requirements

- WordPress 5.8 or later
- PHP 8.0 or later
- CSV and XLSX imports are supported. Legacy XLS files require PhpSpreadsheet to be available on the server.

## Download

Download the ready-to-install ZIP from the [GitHub Releases](https://github.com/mrwan-bg/kashida-branch-locator/releases) page. Choose the `kashida-branch-locator-v2.5.22.zip` release asset rather than GitHub's automatically generated **Source code** archives.

## Installation

1. Download the plugin ZIP from GitHub Releases.
2. In WordPress, open **Plugins → Add New Plugin → Upload Plugin**.
3. Upload the ZIP, install it, and activate **Kashida Branch Locator**.
4. Add branch records or import a CSV/XLSX file.
5. Add a shortcode to a page. For example:

   ```text
   [kashida_branch_locator]
   ```

## Shortcode examples

```text
[kashida_branch_locator layout="table"]
[kashida_branch_locator layout="cards"]
[kashida_branch_locator layout="accordion"]
[kashida_branch_locator layout="list"]
[kashida_branch_locator layout="map"]
[kashida_branch_locator layout="map-table" search="true" pagination="true"]
[kashida_branch_locator city="Tripoli"]
[kashida_branch_locator group="grp_xxxxxxxx"]
```

See [`SHORTCODE-USAGE.txt`](kashida-branch-locator/SHORTCODE-USAGE.txt) for more examples.

## External services and privacy

Map layouts load map tiles from OpenStreetMap when used. A visitor's browser requests those tiles directly, so OpenStreetMap may receive the visitor's IP address and normal HTTP request information. The plugin also offers a user-initiated Google Maps directions link. It does not include analytics, advertising, telemetry, licensing checks, or automatic tracking, and does not contact Kashida servers for normal operation.

- [OpenStreetMap](https://www.openstreetmap.org/)
- [OpenStreetMap tile usage and copyright](https://www.openstreetmap.org/copyright)
- [OpenStreetMap Foundation Privacy Policy](https://osmfoundation.org/wiki/Privacy_Policy)
- [Google Maps Privacy Policy](https://policies.google.com/privacy)

## Project files

- Plugin source: [`kashida-branch-locator/`](kashida-branch-locator/)
- Changelog: [`CHANGELOG.md`](CHANGELOG.md)
- Contributing: [`CONTRIBUTING.md`](CONTRIBUTING.md)
- License: [`LICENSE`](LICENSE)

## Support and contributions

- [Report a bug](https://github.com/mrwan-bg/kashida-branch-locator/issues/new?template=bug_report.md)
- [Request a feature](https://github.com/mrwan-bg/kashida-branch-locator/issues/new?template=feature_request.md)
- [View releases](https://github.com/mrwan-bg/kashida-branch-locator/releases)

## Credits

Designed and developed by **Kashida Media Services**.  
Website: https://kashida.ly/
