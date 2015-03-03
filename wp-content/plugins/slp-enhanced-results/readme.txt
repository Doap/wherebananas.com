=== Store Locator Plus : Enhanced Results ===
Plugin Name:  Store Locator Plus : Enhanced Results
Contributors: charlestonsw
Donate link: http://www.storelocatorplus.com/product/slp4-enhanced-results/
Tags: search form, google maps, results
Requires at least: 3.3
Tested up to: 4.0
Stable tag: 4.2.02

A premium add-on pack for Store Locator Plus that adds enhanced search results to the plugin.

== Description ==

Get more control of the search results on Store Locator Plus with this enhanced results add-on.

= Related Links =

* [Store Locator Plus](http://www.storelocatorplus.com/product/store-locator-plus/)
* [Other CSA Plugins](http://profiles.wordpress.org/charlestonsw/)

== Installation ==

= Requirements =

* Store Locator Plus: 4.2.26+
* WordPress: 3.4+
* PHP: 5.1+

= Install After SLP =

1. Go fetch and install Store Locator Plus version 4.1 or higher.
2. Purchase this plugin from CSA to get the latest .zip file.
3. Go to plugins/add new.
4. Select upload.
5. Upload the slp-enhanced-results.zip file.

== Frequently Asked Questions ==

= What are the terms of the license? =

The license is GPL.  You get the code, feel free to modify it as you
wish.  We prefer that our customers pay us because they like what we do and
want to support our efforts to bring useful software to market.  Learn more
on our [CSA License Terms](http://www.storelocatorplus.com/products/general-eula/).

== Changelog ==

We update about once per month or more frequently as needed.

Visit the [Store Locator Plus Website for details](http://www.storelocatorplus.com/tag/slp4-changelog/).

= 4.2.02 (Jan-20-2015) =

* Fix: Fix the AJAX order by options.

= 4.2.01 (Jan-19-2015) =

* Fix: Make sure the rank and featured fields are added on ANY version of Enhanced Results.

= 4.2.0 (Jan-2015) =

* Enhancement: Update to use version 4.2 framework.
* Enhancement: Added email output selector for results: email label direct link, email address direct link, popup email form.
* Enhancement: Added an improved popup email form for email links.  Uses jQuery Dialog.  Uses a more secure AJAX handler for processing.
* Enhancement: Allow a WPML-compatible title to be set for the popup email form dialogue.
* Enhancement: Allow WPML-compatible text to be added for the from, subject, and message placeholders on the email popup form.
* Note: Email popup form uses the store ID to set the "to" address preventing email spoofing on the email form via hacker scripts.

= 4.1.14 (Nov-2014) =

* Enhancement (4.1.14): Add Swedish (sv_SE) translation by [Piani Sweden AB](http://piani.se).
* Fix: Further refinement of the extended data testing to fix map results issues.
* Fix: Work around the extended data NULL values for featured and rank sorting.
* Change: ZERO rank is now considered "unranked" same as null or blank. Start rank at 1 for highest-ranked items.

= 4.1.11 =

* Note: Requires SLP 4.1.31+
* Fix: Do not allow featured where clause to be used if extended data flags are not setup properly.
* Fix: typo in "show in radius" help message
* Enhancement: Update Dutch (nl_NL) translation.
* Enhancement: Added Spanish (es_ES) and Serbian (sr_RS) translaction from [Ogi Djuraskovic](http://firstsiteguide.com).
* Change: Show Country default to "on".  That is the default layout for the base plugin.

= 4.1.10 =

* Change: Make the foreign translated data extensions more intelligent when upgrading to 4.1.09+
* Fix: shortcode attributes take precedence over global UX settings.
* Enhancement: add hide_results="true|false" option to SLPLUS shortcode.  All SLPLUS shortcode attributes require Force Load JavaScript to be turned OFF.

= 4.1.09 =

* Fix: Featured and Rank extended data field names are now 'featured' and 'rank', NOT translated into their native language.

= 4.1.07 =

* Enhancement: [SLPLUS shortcode](http://www.storelocatorplus.com/support/documentation/store-locator-plus/shortcodes/) has a new immediately_show_locations attribute.
* Enhancement: [SLPLUS shortcode](http://www.storelocatorplus.com/support/documentation/store-locator-plus/shortcodes/) has a new order_by attribute.
* Enhancement: Locations that are "unfeatured" no longer show a 0 status in the featured column on manage locations.
* Enhancement: Featured, Rank fields are now part of the Pro Pack CSV export.


= 4.1.03 =

* Fix: force extended data tables to update.
* Enhancement: Featured locations can be forced to always show regardless of radius.
* Enhancement: Bulk action feature/remove featured locations.
* Enhancement: CSV Import now allows featured field to be imported.
* Enhancement: CSV Import now allows rank field to be imported.


= 4.1.01 =

* Fix: fix the order by options on the featured, rank fields.
* Enhancement: Update Dutch (nl_NL) language files.
* Enhancement: Update German (de_DE) language files.

= 4.1.00 =

* Change: Updated to work with SLP 4.1, Super Extendo no longer required for featured/ranked locations.

