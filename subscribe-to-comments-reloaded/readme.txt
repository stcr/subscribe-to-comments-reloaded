=== Subscribe To Comments Reloaded ===
Contributors: coolmann, Reedyseth, raamdev
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=XF86X93FDCGYA&lc=US&item_name=Datasoft%20Engineering&item_number=DI%2dSTCR&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: subscribe, comments, notification, subscription, manage, double check-in, follow, commenting
Requires at least: 2.9.2
Tested up to: 3.8
Stable tag: 2.0.6

Subscribe to Comments Reloaded allows commenters to sign up for e-mail notifications of subsequent replies.

== Description ==
Subscribe to Comments Reloaded is a robust plugin that enables commenters to sign up for e-mail notification of subsequent entries. The plugin includes a full-featured subscription manager that your commenters can use to unsubscribe to certain posts or suspend all notifications. It solves most of the issues that affect Mark Jaquith's version, using the latest Wordpress features and functionality. Plus, allows administrators to enable a double opt-in mechanism, requiring users to confirm their subscription clicking on a link they will receive via email.

## Requirements
* Wordpress 2.9.2 or higher
* PHP 5.1 or higher
* MySQL 5.x or higher

## Main Features
* Does not modify Wordpress core tables
* Easily manage and search among your subscriptions
* Imports Mark Jaquith's Subscribe To Comments (and its clones) data
* Messages are fully customizable, no poEdit required (and you can use HTML!)
* Disable subscriptions for specific posts
* Compatible with [Fluency Admin](http://deanjrobinson.com/projects/fluency-admin/) and [QTranslate](http://wordpress.org/extend/plugins/qtranslate/)

== Installation ==

1. If you are using Subscribe To Comments by Mark Jaquith, disable it (no need to uninstall it, though)
2. Upload the entire folder and all the subfolders to your Wordpress plugins' folder
3. Activate it
5. Customize the Permalink value under Settings > Subscribe to Comments > Management Page > Management URL. It **must** reflect your permalinks' structure
5. If you don't see the checkbox to subscribe, you will have to manually edit your template, and add `<?php if (function_exists('subscribe_reloaded_show')) subscribe_reloaded_show(); ?>` somewhere in your `comments.php`
6. If you're upgrading from a previous version, please **make sure to deactivate/activate** StCR

== Frequently Asked Questions ==

= Aaargh! Were did all my subscriptions go? =
No panic. If you upgraded from 1.6 or earlier to 2.0+, you need to deactivate/activate StCR, in order to update the DB structure

= How do I create a 'real' management page? =
Please refer to [this page](http://lab.duechiacchiere.it/index.php?topic=71.0) for a detailed step-by-step description on how to do that

= Can I customize the layout of the management page? =
Yes, each HTML tag has a CSS class or ID that you can use to change its position or look-and-feel

= How do I disable subscriptions for a given post? =
Add a custom field called `stcr_disable_subscriptions` to it, with value 'yes'

= How do I add the management page URL to my posts? =
Use the shortcode `[subscribe-url]`, or use the following code in your theme:
`if(function_exists('subscribe_reloaded_show')) echo '<a href="'.do_shortcode('[subscribe-url]').'">Subscribe</a>";`

= Can I move the subscription checkbox to another position? =
Yes! Just disable the corresponding option under Settings > Comment Form and then add the following code where you want to display the checkbox:
`<?php if (function_exists('subscribe_reloaded_show')) subscribe_reloaded_show(); ?>`

== Screenshots ==

1. Manage your subscriptions
2. Use your own messages to interact with your users
3. Customize the plugin's behavior

== Changelog ==

= 2.0.6 =
* Updated: Updated the contact information on every laguage file, some links were missing.
* Fixed: The Spanish translation had some missing text.

= 2.0.5 =
* Added: Since the authorship of the plugin has changed I added the correct information of the contact in order to have a faster response to the issues.

= 2.0.4 =
* Added: Dutch translation fixes provided by [Martijn Chel](http://www.martijnchel.com)
* Fixed: There was vulnerability in the form where the URI was added.

= 2.0.3 =
* I would like to thank Andreas for contributing to the project and fixing some issues with the plugin

= 2.0.2 =
* Added: option to automatically subscribe authors to their posts (improves Wordpress' default alert system, thank you [Julius](http://wordpress.org/support/topic/plugin-subscribe-to-comments-reloaded-does-the-post-author-automatically-get-subscribed-to-comments))
* Added: number of subscriptions per post in the Posts page
* Added: Serbian and Indonesian localization (thank you [Anna](http://www.sneg.iz.rs/) and [The Masked Cat](http://themaskedcat.tk))
* Fixed: bug in daily purge SQL command
* Fixed: bug with international characters (thank you Pascal)
* Updated: you can now edit a single subscription's status without having to change the email address
* Updated: more localizations are now up-to-date, thank you!

= 2.0.1 =
* Maintenance release: 2.0 shipped with a bunch of annoying bugs, sorry about that!
* Added: option to not subscribe in 'advanced mode' (thank you [LincolnAdams](http://wordpress.org/support/topic/replies-only-broken))
* Added: subscriptions count for each post (All Posts panel)
* Added: option to disable the virtual management page, for those [having problems](http://lab.duechiacchiere.it/index.php?topic=71.0) with their theme
* Fixed: subscriptions to replies only were not working properly, fixed (thank you [LincolnAdams](http://wordpress.org/support/topic/replies-only-broken))
* Fixed: some warning popping up with WP_DEBUG mode enabled
* Updated: most localizations are now up-to-date, thank you everybody!

= 2.0 =
* StCR does not use a separate table anymore, making it fully compatible with Wordpress 'network' environments! YAY!
* Added: option to prevent StCR from adding the subscription checkbox to the comment form (useful for those who want to display the box in different place on the page)
* Added: you can now disable subscriptions on specific posts, by adding a custom filed `stcr_disable_subscriptions` set to 'yes'
* Added: double opt-in is only required once, users with at least one active subscription will automatically get approved
* Added: administrators can add new subscriptions on-the-fly
* Added: if Akismet is detected, it will now be used to check those who subscribe without commenting
* Added: new shortcode to add the management page URL to your posts/widgets (thank you [Greg](http://wordpress.org/support/topic/plugin-subscribe-to-comments-reloaded-plugin-does-not-create-table))
* Added: option to enable "advanced" subscription mode, where users can choose what kind of subscription they want to activate (all, replies only)
* Added: new localizations
* Added: security checks when uninstalling the plugin
* Updated: reorganized and polished the CSS classes and ID's on the management page
* Updated: registered users are not required to confirm their subscriptions anymore (if double opt-in is enabled)
* Fixed: a problem with Gmail addresses containing a + sign in them
* Fixed: a bug with HTML attributes in the field "custom HTML for the checkbox" (thank you [travelvice](http://wordpress.org/support/topic/custom-html-quotes-problem-php-ecape-characters))
* Fixed: a bug causing some themes to not display the management page

== Language Localization ==

Subscribe to Comments Reloaded can speak your language! If you want to provide a localized file in your
language, use the template files (.pot) you'll find inside the `langs` folder,
and [contact me](http://behstant.com/negocio/contact.php) once your
localization is ready. Currently, we support the following languages:

* Danish - [Torben Bendixen](http://www.freelancekonsulenten.dk/)
* Dutch - [Muratje](http://www.muromedia.nl/)
* French - [Anthony](http://imnotgeek.com/), Goormand, Maxime
* German - [derhenry](http://www.derhenry.net/2010/subscribe-to-comments-reloaded/), [Stefan](http://www.beedy.de/)
* Indonesian - [The Masked Cat](http://themaskedcat.tk)
* Italian - myself
* Norwegian - [Odd Henriksen](http://www.oddhenriksen.net/)
* Polish - [Robert Koeseling](http://www.katalogpodkastow.pl), [Filip Cierpich](http://keepmind.eu/)
* Portuguese, Brazil - [Ronaldo Richieri](http://richieri.com), [ClassiNoiva](http://www.classinoiva.com.br), [Luciano](http://litemind.com/)
* Portuguese, Portugal
* Russian - [Marika Bukvonka](http://violetnotes.com)
* Serbian - [Anna Swedziol](http://www.sneg.iz.rs/)
* Spanish - [TodoWordPress team](http://www.todowp.org/), [Juan Luis Perez](http://www.juanluperez.com/)
* Turkish - [MaD, Kali](http://www.dusunsel.com/)
