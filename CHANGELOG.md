# Changelog

All notable changes to the Solace phpBB style will be documented in this file.

## [1.1.0] - 2025-01-07

### Added
- Full phpBB 3.3.x compatibility
- Improved FontAwesome integration using phpBB's built-in system
- Enhanced icon font support with dedicated `icons.css`

### Changed
- Updated CSS selectors from `dl.icon` to `dl.row-item` for phpBB 3.2+ compatibility
- Migrated FontAwesome loading to use `{T_FONT_AWESOME_LINK}` template variable
- Improved dropdown menu functionality and styling
- Updated navbar styling for better phpBB 3.3 integration

### Fixed
- Forum icons not displaying properly in phpBB 3.2+
- FontAwesome icons missing in navigation and dropdowns
- Dropdown menus staying open by default
- Compatibility issues with phpBB 3.3 template structure

### Removed
- Manual FontAwesome CDN/local file loading (now uses phpBB core)
- Redundant local FontAwesome files

## [1.0.0] - 2016-02-03

### Added
- Initial release of Solace phpBB style
- 6 predefined color presets
- Multilevel menu system with 4-level dropdowns
- Slideshow functionality
- Devlom Configurator extension
- Icons Editor with 50+ forum/topic states
- 700+ Google Fonts support
- Social icons integration
- Responsive design for mobile devices
- RTL language support
- FontAwesome and ET Line-Style icons
- Template inheritance from prosilver