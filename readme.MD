=== Simple Block Builder - Custom ACF Blocks Made Easy ===
Contributors: thecodebeast
Tags: acf, gutenberg, blocks, builder, custom blocks
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build custom Gutenberg blocks directly in the WordPress dashboard using ACF Pro. Write HTML, PHP, CSS, and JS instantly.

== Description ==

**Simple Block Builder** allows developers and site builders to create custom Gutenberg blocks directly from the WordPress admin area. Powered by **Advanced Custom Fields (ACF) Pro**, this plugin removes the need to create manual block registrations or touch your theme's `functions.php` file for every new block.

Simply create a new "Custom Block", define your unique slug, choose an icon, and write your code.

### Key Features

- **Zero Boilerplate:** No need to manually register blocks via PHP.
- **In-Dashboard Editor:** Write HTML/PHP templates, CSS, and JavaScript directly in the editor.
- **Asset Management:** Automatically generates and enqueues unique CSS and JS files for each block.
- **ACF Integration:** Uses ACF's powerful rendering engine.
- **Icon Picker:** Choose from WordPress Dashicons for your block icon.

### Requirements

- **Advanced Custom Fields Pro** (Plugin must be active).

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Ensure **ACF Pro** is installed and active.
4.  Navigate to "Custom Blocks" in the sidebar to start building.

== Frequently Asked Questions ==

= Does this work with the free version of ACF? =
No, this plugin relies on `acf_register_block_type`, which is a feature of ACF Pro.

= Where is the CSS/JS stored? =
Generated assets are stored in `wp-content/uploads/simple-block-builder/`.

= Can I use PHP in the template? =
Yes, the template field supports PHP execution, allowing you to use functions like `get_field()` to retrieve ACF data attached to the block instance.

== Changelog ==

= 1.0 =

- Initial release.
