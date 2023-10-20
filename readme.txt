=== PS Fundraising ===
Contributors: DerN3rd (WMS N@W)
Donate link: https://n3rds.work/spendenaktionen/unterstuetze-unsere-psource-free-werke/
Tags: fundraising, spenden, crowdfunding
Requires at least: 3.0
Tested up to: 5.6.1
Stable tag: 2.7.5
License: GPLv2 or later

Fundraising ist unsere Weiterentwicklung des von WPMUDEV eingestellten Fundraising-Plugins.
Erstelle Spendenbuttons, komplexe Crowdfundingkampagnen oder lasse Deine Besucher Deine Projekte unterstützen.

== Description ==

Fundraising ist unsere Weiterentwicklung des von WPMUDEV eingestellten Fundraising-Plugins.
Erstelle Spendenbuttons, komplexe Crowdfundingkampagnen oder lasse Deine Besucher Deine Projekte unterstützen.

=== Spende oder Crowdfunding ===

Crowdfinanziere Dein nächstes Projekt mit Finanzierungszielen und Prämienpaketen oder erstelle schnell eine einzelne Spendenseite mit wiederkehrenden Zahlungsoptionen.

=== Mehr Geld für Dein Projekt ===

Services wie Kickstarter und Indiegogo erheben bis zu 11% Verwaltungsgebühr und Bearbeitungsgebühr. 
Vergiss die unverschämten Gebühren und stecke die Gelder stattdessen direkt in Deine gemeinnützige, Indie-Plattenveröffentlichung oder verrückte Erfindung.

=== Leistungsstark und einfache Einrichtung ===

Wenn Du nach einem leistungsstarken Fundraising-System mit Tausenden von Einstellungen suchst, ist dies nicht der Fall. 
Andere Plugins können das. 
Fundraising fügt nur die Power-Features für einfaches Crowdfunding und schnelles Setup hinzu.

[POWERED BY PSOURCE](https://n3rds.work/psource_kategorien/psource-plugins/)

=== Hilfe und Support ===

[Projektseite](https://n3rds.work/piestingtal_source/ps-fundraising/)
[GitHub](https://github.com/piestingtal-source/ps-fundraising)

== Mehr PSOURCE ==

= Finde mehr Piestingtal.Source =

Wirf einen Blick in unser [PSOURCE Sortiment](https://n3rds.work/psource_kategorien/psource-plugins/) und hole noch mehr aus Deinem WordPress/ClassicPress!

Halte Dich mit unserem [Newsletter](https://n3rds.work/webmasterservice-n3rdswork-digalize-das-piestingtal/newsletter-management/) über unsere Piestingtal.Source informiert!

== Entwickler ==

Du kannst im Stammordner Deines aktiven Themas neue Dateien erstellen, die beim Anzeigen einer Spendenaktion verwendet werden.

Es gibt 3 Arten von Vorlagen, die Du erstellen kannst.

=== Vorlagenhierarchie ===

Einzelne Spendenaktion
wdf_funder-{ID}.php
wdf_funder-{slug}.php
wdf_funder.php
 
Kasse für Spendenaktionen
wdf_checkout-{ID}.php
wdf_checkout-{slug}.php
wdf_checkout.php

Bestätigungsseite für die Spendenaktion
wdf_confirm-{ID}.php
wdf_confirm-{slug}.php
wdf_confirm.php


=== Benutzerdefinierte Vorlagenfunktionen ===

Du kannst den Speicherort der Template-Funktionen, die in Fundraising geladen werden, an einen externen Speicherort ändern.
Dadurch wird sichergestellt, dass alle benutzerdefinierten Änderungen, die Du an Deinen Vorlagenfunktionen vornimmst, bei zukünftigen Aktualisierungen des Plugins nicht beschädigt werden.

Füge in Deiner Datei wp-config.php eine neue define-Anweisung hinzu, die eine Zeichenfolge enthält, die auf den Speicherort verweist.
define('WDF_CUSTOM_TEMPLATE_FUNCTIONS','/full-server-path/and/filename.php');


=== Benutzerdefinierte Stile ===

Füge eine beliebige CSS-Datei zu Deinem Fundraising-/styles/-Ordner hinzu, damit sie in Deinen Einstellungen verfügbar ist.

Du kannst auch einen Ordner namens "wdf-styles" in Deinem wp-content-Ordner erstellen

ODER

Du kannst einen externen Speicherort mit dem folgenden Code in Deiner wp-config.php-Datei festlegen.

define('WDF_EXTERNAL_STYLE_DIRECTORY','/full/server/path/to/styles/')

Standardmäßig ist der Name des Stils der Name der Datei. Du kannst dies manuell mit dem Filter "wdf_custom_style_name" anpassen

=== Beispiel: ===

add_filter('wdf_custom_style_name', 'my_custom_filter_style_name', 10, 2);

function my_custom_filter_style_name($name, $file_name) {
	if($file_name == 'my-file.css') {
		$name = "Mein benutzerdefinierter Stil";
	}
	return $name;
}

== Hilf uns ==

Viele, viele Kaffees konsumieren wir während wir an unseren Plugins und Themes arbeiten.
Wie wärs? Möchtest Du uns mit einer Kaffee-Spende bei der Arbeit an unseren Plugins unterstützen?

= Unterstütze uns =

Mach eine [Spende per Überweisung oder PayPal](https://n3rds.work/spendenaktionen/unterstuetze-unsere-psource-free-werke/) wir Danken Dir!

Halte Dich mit unserem [Newsletter](https://n3rds.work/webmasterservice-n3rdswork-digalize-das-piestingtal/newsletter-management/) über unsere Piestingtal.Source informiert!

== Changelog ==

= 2.7.5 =

* Hinzugefügt: Material CSS Style
* jQuery fixes
* PhP8 Fixes

= 2.6.9 =

Behoben: Depecated jQuery ".click()"
Behoben: Depacated "create_function"

= 2.6.4.9 =
Fixed remaining deprecated constructors
Fixed rewards count on pledge status change
Improved UX for configuring simple donations with goals

= 2.6.4.8 =
Fixed issue causing redirects to a blank page when pledge is submitted
Fixed issue forcing no funding goal for advanced crowd funding
Fixed deprecated constructor for one of the classes

= 2.6.4.7 =
Fixed BuddyPress compatibility issues
Fixed manual payment country field issues

= 2.6.4.6 =
Removed PHP 4 constructor

= 2.6.4.5 =
Fixed issues with counting reward limits

= 2.6.4.4 =
Fixed issues with address colection for manual gateway
WPMUDEV dashboard notice update

= 2.6.4.3 =
Fixed "Times Up!" issues with simple donation fundraising

= 2.6.4.2 =
Improved FE styling (please check compatibility with active theme)
Improved UX for jumping between Simple and Advanced fundraising type
Improved fundraising start/end date handling
Improved PP error handling
Added ajax check for delayed pledges
Enabled address collecting without rewards
Fixed typo

= 2.6.4.1 =
Improved Upfront compatibility
Added  ability to hide standard Fundraising Panel (may be useful in some scenarios)
Fixed widgets related PHP notices
Fixed BP related PHP strick notices

= 2.6.4 =
Fixed compatibility with PayPal changes
Fixed widget's edit screen markup

= 2.6.3 =
Fixed issues with tutorial

= 2.6.2 =
Added support for refunds in recurring payments
Fixed recurring payments amount problem for currencies different than US Dollars

= 2.6.1.9 =
Improved UX when switching to advanced payments
Fixed problems with address storing when using advanced payments

= 2.6.1.8 =
Fixed is_subdomain_install error

= 2.6.1.7 =
Added possibility to collect address even when rewards are disabled
Fixed some permalink issues
Fixed storing of custom data for PayPal 

= 2.6.1.6 =
Added default WP filter for titles in widgets

= 2.6.1.5 =
Added missing jquery ui images

= 2.6.1.4 =
Fixed SSL loading issues

= 2.6.1.3 =
Changed IPN url for better server compatibility
Fixed pledge button for touch devices
Fixed recurring payments
Fixed rewards counting after donation removal

= 2.6.1.2 =
Fixed top pledges sorting

= 2.6.1.1 =
Fixed counting of rewards left

= 2.6.1 =
Added pledges panel widget and shortcode to list recent or top pledges
Added ability to set custom message for situation when pledge was not found
Improved UX for creating new Fundraisers
Improved respect for label settings inside admin panel
Fixed PHP warnings
Fixed time left counting for Fundraisers
Fixed session cleaning

= 2.6.0.5 =
Added back front permalink option on main site in multisite
Fixed buddypress activity item being added for unlogged users
Other small improvements

= 2.6.0.4 =
Fixed missing translation strings

= 2.6.0.3 =
Fixed missing ")" causing php error

= 2.6.0.2 =
Fixed rounding of raised amount
Fixed problems with older Internet explorers
Fixed notifications about minimal amount
Fixed limit of listed fundraisers in widget
Small visual improvements

= 2.6.0.1 =
Update plugins for WP 3.8. UI changes. 

= 2.6 =
Added ability to collect address
Added ability to limit number of rewards
Small improvements to UI
Fixed problem with fundraisers list widget

= 2.5.3 =
Fixed forms not being closed for custom buttons in some configurations

= 2.5.2 =
Added missing translation
Unnecessary string removal

= 2.5.1 =
Removed "front" permalink option for multisite
Fixed permalink problem with /%category%/ inside
Other small improvements

= 2.5 =
Added ability to control access to fundraisings features for all available user types
Fixed email subject in fundraising not saving correctly

= 2.4.2 =
Allows shortcodes inside fundraising if ID is not the same as current fundraising

= 2.4.1 =
Fixed problem with fundraising content not being displayed

= 2.4 =
Disables fundraising shortcodes inside fundraising
PayPal Support for all UTF-8 characters

= 2.3.9 =
Fixed styles on older browsers

= 2.3.8 =
Fixed bug with fundraising panel not being displayed when only specific fundraising widget is configured in sidebar
Fixed mistype in manual payment getaway

= 2.3.7 =
Allowed HTML tags in reward description
Added information about choosen reward(id) in admin panel
Changed paypal url for possible ipn problem fixes
Fixed security issue

= 2.3.6 =
Replaced .live jQuery functions with .on
Fixed small UI issue in chrome for fundraising type chooser
Fixed "Add reward" button not working after deleting all rewards before last

= 2.3.5 =
Fixed manual payment status not saving
Changed/fixed behavior of permalinks and rewrite flushing
Added ability to disable "front" in permalinks from main MU site ("blog/" by default)
Added option to select default getaway
Added workaround for EURO sign in email

= 2.3.1 =
Added manual payment getaway

= 2.2.5 =
Fixed php notices about undefined indexes
Fixed notifications from paypal to wordpress about payments with special characters in payment details ( ', " ...)

= 2.2.4 =
Added new external style locations. Check fundraising-templates.txt for more info
Added new filter and reference label option for standard payments and simple donation buttons

= 2.2.3 =
Rewrite Flush Issue Fixed
Fixed issue with subscriber error in admin panel
Currency characters are properly decoded for thank you emails
New "wdf_paypal_gateway_standard_item_number" filter for changing the "Reference" on checkout

= 2.2 =
Fundraising slug now functional with multisite subdirectory.
Fundraiser menu ancestry now works properly when using menus in your theme.
Fixed problem concerning per checkout types breaking panel display.
Adjusted all concatenated translation strings to sprintf() strings.
Fixed action name not displaying in fundraising widget


= 2.1.8 =
Fixed issue with fundraisers with goals not showing in shortcode generator
Fixed problem with styles not loading in particular situations
Custom css styling available, but simply adding a css file to /styles folder, see fundraising-templates.txt for more info

= 2.1.7 =
Shortcode generator now inserts properly into both the HTML and Visual editor
Fixed problem with above/below content option not displaying the current setting
Unterstützer Label now displays a singular label when only one pledge has been taken
Fixed issue with displaying email settings correctly

= 2.1.6 =
Fixed bug with not allowing more than 10 rewards

= 2.1.5 =
Fundraising now has it's own metabox in your Theme's Appearance Menus
Multiple php warning fixes
Corrected x.com links for creating an application
Added the ability to set checkout type per fundraiser
Fixed currency display issues
Better display of options on the shortcode generator pop-up
Small change to menu order due to compatibility errors with other plugins and hosting services
New fundraising-templates.txt file in root folder that explains the template hierarchy


= 2.1.1 =
Fixed Manual Unterstützung Payments
Rewards and Goals can now be set independently of each other


= 2.1 =
Fixed Canadian Currency Issue
New plugin labeling system
Fixed HTML structure error on checkout pages
Added global custom CSS box in presentation settings (better styles in next release)
Added Minutes to wdf_time_left()
Changed Featured Fundraisers widget to Fundraiser List


= 2.0-RC-3 =
Paypal error codes now correctly display if redirection fails.

Fixed headers already sent error with certain themes.

Simple Fundraisers can now use rewards and goals again
- Advanced Payments still may not change goals and rewards if the fundraiser has been published and pledges have already been taken.

New wdf_has_date_range() template function.

Added new option in presentation settings for checking out directly from the fundraising panel.


= 2.0-RC-2 =
Fixed issues with PayPal App ID not saving.

Fixed problem with PayPal redirect on certain server setups


= 2.0-RC-1 =
New Payment Gateway API
- Fundraisers are now split into two types: Simple and Advanced
- Simple payments are donations that are accepted immediately. ( No Goals or Rewards )
- Advanced Payments are pre-approved and only processed after the completion of the fundraiser's goal. ( Goals and Rewards allowed )

Donations are now refered to as pledges.
- 4 new pledge statuses are available.  ( Complete, Approved, Canceled, Refunded )

New Fundraising Panel
- Use either a shortcode or widget to display relevant fundraiser information

New Reset option for clearing all fundraising data
- add define('WDF_ALLOW_RESET',true); in your wp-config file to add an extra reset tab to your settings page.

Limited BuddyPress Integration
- Users are allow to publicly display their plegde as an activity item if they choose to do so.  This option display requires that BuddyPress be activated on your site.

CSS style containers are now all <div> elements to allow for easier customization.

New permalink structure
- Each fundraiser now contains a checkout and confirmation page.

Template functions can now be overridden using the action 'wdf_custom_template_functions'
- fundraiser.php line: 1521

Fixed shortcode media button errors

New custom template structure for your theme
- wdf_funder-{$name/$id}.php
- wdf_checkout-{$name/$id}.php
- wdf_confirm-{$name/$id}.php

Addition of several action and filter hooks for external plugins or theme function files.