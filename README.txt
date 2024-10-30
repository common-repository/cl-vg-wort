=== VG Wort - Report texts (automatically) online ===
Contributors: CrayssnLabs
Donate link: https://cl.team
Tags: automation, vg wort, pay authors
Requires at least: 5.2
Tested up to: 6.5
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin automatically integrates the VG Wort tracking pixels and submits them for verification.

== Description ==

This plugin analyses all published pages according to the criteria of the VG Wort and integrates a data protection compliant pixel counter to anonymously count the number of hits on the texts. These texts are automatically reported to the VG Wort as soon as a certain number has been reached.

== Installation ==

1. Upload `cl-vg-wort.zip` through the WordPress 'Plugins' area to install the plugin
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set the api key through the plugin options page

== Changelog ==

= 1.0.4 =
* Optimized pixel integration, prevent double integration
* Overwrite existing pixel if there are another integrated pixel

= 1.0.3 =
* Used permalink instead of the guid

= 1.0.2 =
* Changed required php version 8.0+

= 1.0.1 =
* Changed "Contributors"

= 1.0.0 =
* Automatic reporting of texts has been added
* Expansion of the overview of all VG-Wort trademarks
* Expansion of the user profile to include individual VG-Wort numbers

= 0.3.0 =
* removed unneeded esc_js

= 0.2.2 =
* removed unneeded esc_js

= 0.2.1 =
* fixed problem in the deployment process (added missing files to svn)

= 0.2.0 =
* Plugin version has been adapted to allow switching from privately hosted plugin variant to the one hosted at wordpress.org

= 0.0.3 =
* added page scan (find already integrated tracking pixels)
* testing wp svn

= 0.0.2 =
* changed author
* testing wp svn

= 0.0.1 =
* first release
* testing wp svn