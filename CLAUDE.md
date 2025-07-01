# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a phpBB 3.3 compatible style called "Solace" with an accompanying configuration extension. The project consists of three main components:

1. **`solace/`** - The main phpBB style (inherits from prosilver)
2. **`devlom/configurator/`** - A phpBB extension providing GUI configuration 
3. **`documentation/`** - HTML-based documentation portal

## Development Commands

This project follows traditional phpBB development patterns without modern build tools:

- **No build process** - CSS/JS files are served directly
- **No linting/testing commands** - Files are edited directly
- **No package.json or build scripts** - Traditional phpBB file structure

When making changes, test by:
1. Installing the style in a phpBB instance
2. Testing the configurator extension functionality
3. Verifying responsive design across devices

## Architecture Overview

### Style System
- **Template inheritance**: Inherits from prosilver, overrides only essential templates
- **CSS architecture**: Modular CSS with Foundation framework integration
- **Configuration**: YAML-based system with 6 predefined color presets
- **Assets**: Icons, fonts, and images served from `theme/` directory

### Key Configuration Files
- `solace/config.yaml` - Main style configuration (colors, fonts, layout)
- `solace/presets.yaml` - Six predefined color schemes
- `solace/style.cfg` - phpBB style metadata
- `solace/content.yaml` - Menu and content structure references

### Extension Architecture
- **PHP Extension**: Located in `devlom/configurator/` (compatible with phpBB 3.1-3.3)
- **Service container**: Uses Symfony-style dependency injection
- **Event system**: Integrates with phpBB's event dispatcher
- **Admin panel**: ACP module for style configuration

### Template Structure
Templates are minimal due to inheritance:
- `solace/template/overall_header.html` - Main header template
- `solace/template/overall_footer.html` - Footer template  
- `solace/template/index_body.html` - Homepage template

### CSS Structure
- `solace/theme/stylesheet.css` - Main entry point with @import statements
- Modular CSS files: `common.css`, `content.css`, `buttons.css`, `forms.css`
- `responsive.css` - Mobile/tablet responsive styles
- Foundation CSS framework integration

### Configuration System
- **Multi-level menus**: 4-level dropdown navigation via YAML
- **Icon system**: Supports FontAwesome and LineIcons
- **Slideshow**: Customizable content slides
- **Presets**: Quick color scheme switching
- **RTL support**: Right-to-left language compatibility

## Important Notes

- Style inherits from prosilver - avoid breaking compatibility
- Configuration changes require phpBB cache clearing
- Extension follows phpBB 3.1+ standards
- Templates use Twig syntax
- CSS changes are immediate (no compilation needed)
- Test configuration changes through the ACP module

## File Locations

- Style files: `solace/`
- Extension files: `devlom/configurator/`
- Configuration: `solace/*.yaml`
- Templates: `solace/template/`
- Stylesheets: `solace/theme/`
- Documentation: `documentation/`

## Memories
- Solace inherits templates (html files) from prosilver (path to view: /var/www/html/phpbb/styles/prosilver)