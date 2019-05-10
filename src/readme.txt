=== Subscribe To Comments Reloaded ===
Author: WPKube
Contributors: WPKube
Tags: comments, subscribe, subscribe to comments, subscribe to comments reloaded, email, email notification, subscriptions, commenting, reply, reply to comments, post notification, comment notification, automatic comment notification, email signup
Plugin URI: http://subscribe-reloaded.com/
Requires at least: 4.0
Requires PHP: 5.6
Requires MySQL: 5.6
Tested up to: 5.2
Stable tag: 190510
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Subscribe to Comments Reloaded allows commenters to sign up for e-mail notifications of subsequent replies. Don't miss any comment.

== Description ==
Subscribe to Comments Reloaded is a robust plugin that enables commenters to sign up for e-mail notification of subsequent entries. The plugin includes a full-featured subscription manager that your commenters can use to unsubscribe to certain posts or suspend all notifications. It solves most of the issues that affect Mark Jaquith's version, using the latest Wordpress features and functionality. Plus, allows administrators to enable a double opt-in mechanism, requiring users to confirm their subscription clicking on a link they will receive via email or even One Click Unsubscribe.

## Requirements
* Wordpress 4.0 or higher
* PHP 5.6 or higher
* MySQL 5.x or higher

## Main Features
* Does not modify Wordpress core tables
* Easily manage and search among your subscriptions
* Imports Mark Jaquith's Subscribe To Comments (and its clones) data
* Messages are fully customizable, no poEdit required (and you can use HTML!) with a Rich Text Editor - WYSIWYG
* Disable subscriptions for specific posts
* One Click Unsubscribe
* Get and Download your System information for better support.

== Installation ==


1. If you are using Subscribe To Comments by Mark Jaquith, disable it (no need to uninstall it, though)
2. Upload the entire folder and all the subfolders to your Wordpress plugins' folder. You can also use the downloaded ZIP file to upload it.
3. Activate it
5. Customize the Permalink value under Settings > Subscribe to Comments > Management Page > Management URL. It **must** reflect your permalinks' structure
5. If you don't see the checkbox to subscribe, you will have to manually edit your template, and add `<?php global $wp_subscribe_reloaded; if (isset($wp_subscribe_reloaded)){ echo $wp_subscribe_reloaded->stcr->subscribe_reloaded_show(); } ?>` somewhere in your `comments.php`
6. If you're upgrading from a previous version, please **make sure to deactivate/activate** StCR.
7. You can always install the latest development version by taking a look at this [Video](https://youtu.be/uQwkBciyFGY)

== Frequently Asked Questions ==

= Are there any video tutorials? =
Yeah, I have uploaded a few videos for the following topics:

1. Issues [Updating StCR via WordPress Update](https://youtu.be/Lb6cVx2bBU8)
2. Issues with StCR links see [StCR Clickable Links](https://youtu.be/eFW-2NIRzBA)
3. Issues with empty emails or management messages? see [StCR Management Message](https://youtu.be/yRxOY8yq_cc)
4. Upgrading from the latest development version see [Upgrading](https://youtu.be/uQwkBciyFGY)

= Why my notifications are not in HTML format? =
Don't worry, just go to the Options tab an set to Yes the **Enable HTML emails** option.

= How can I reset all the plugin options? =
There is a new feature called **Safely Uninstall** that allow you to delete the plugin using the WordPress plugin interface. If you have the option set to **Yes** everything but the subscriptions created by the plugin will be wipeout. So after you made sure that you have this option to **Yes** you can deactivate the plugin and the delete it. Now you have to install the plugin via WordPress or Upload the plugin `zip` file and activate it, after this step all your settings will be as default and your subscriptions will remain.
There is a new feature added on the Options tab where you can reset all the settings by using only one click. You can either wipe out all the subscriptions or keep them.

= What can I do if the **Safely Uninstall** does not have any value? =
Just deactivate and activate the plugin and you are all set. The default value will be **Yes**.

= Aaargh! Were did all my subscriptions go? =
No panic. If you upgraded from 1.6 or earlier to 2.0+, you need to deactivate/activate StCR, in order to update the DB structure. After the version 180212 a fix was applied so that you can see all the subscriptions.

= How do I create a 'real' management page? =
Please refer to [this page](https://github.com/stcr/subscribe-to-comments-reloaded/wiki/KB#create-a-real-management-page) for a detailed step-by-step description on how to do that

= Can I customize the layout of the management page? =
Yes, each HTML tag has a CSS class or ID that you can use to change its position or look-and-feel.

= How do I disable subscriptions for a given post? =
Add a custom field called `stcr_disable_subscriptions` to it, with value 'yes'

= How do I add the management page URL to my posts? =
Use the shortcode `[subscribe-url]`, or use the following code in your theme:
`global $wp_subscribe_reloaded; if (isset($wp_subscribe_reloaded)){  echo '<a href="'.do_shortcode('[subscribe-url]').'">Subscribe</a>";`

= Can I move the subscription checkbox to another position? =
Yes! Just disable the corresponding option under Settings > Comment Form and then add the following code where you want to display the checkbox:
`<?php global $wp_subscribe_reloaded; if (isset($wp_subscribe_reloaded)){ echo $wp_subscribe_reloaded->stcr->subscribe_reloaded_show(); } ?>`

= What if after update to the version 141024 I still see plain HTML messages? =
The information of your configuration needs to be updated. Go to the Subscribe to Comments Reloaded settings and click the `Save Changes` button on the tab
where you have you messages with HTML.

= How to generate a new Key for my Site? =
Just go to the Options Panel and click the generate button. By generating a new key you prevent the spam bots to steal your links.

== Screenshots ==

1. Manage your subscriptions
2. Use your own messages to interact with your users
3. Configure the Virtual Management page
4. Customize the notification messages with a the wonderful WordPress Rich Text Editor - WYSIWYG
5. Customize the plugin's behavior
6. Check the number of subscribers in your posts.
7. Manage the subscriptions on the Frontend Side.

== Changelog ==

= v190510 =
* **New** Option to only enable the functionality for blog posts ( option named "Enable only for blog posts" located in WP admin > StCR > StCR Options)
* **Tweak** Info on subscriber and subscriptions amount moved into separate table
* **Fix** Text domain

= v190426 = 
* **New** Info on the amount of subscribers and subscriptions added in WP admin > StCR > StCR System
* **Fix** Text domain (for translations) has been changed to the correct domain (from subscribe-reloaded to subscribe-to-comments-reloaded)
* **Fix** Issue with undefined is_rtl function
* **Fix** Missing blank space between sentences (below comment form when subscribed)
* **Fix** Undefined variable notices for $order_status and $order_dt
* **Fix** Temporarily hidden an unused option in StCR > Management Page to avoid confusion. 
* **Fix** Removed localization for non textual strings
* **Fix** Fixed incorrectly localized textual strings

= v190412 =

* **Fix** Issue with JavaScript code that is supposed to show the form when "StCR Position" is enabled

= v190409 =

* **Fix** Post author was notified of new comments even if they are awaiting approval, no need for this since WordPress itself sends out an email in that case
* **Fix** Post author was notified twice ( if he was subscribed and "subscribe authors" was enabled )
* **Fix** Issue with "StCR Position" option ( for older/outdated themes ) not working properly
* **Fix** Issue with wrong translation in German
* **Tweak** The "Action" select box labels on "Manage Subscriptions" page tweaked to be more descriptive

= v190325 =

* **New** Shortcode for manage page content (to be used on non-virtual management page). The shortcode is [stcr_management_page]
* **Rewrite** New method for downloading system information file
* **Fix** The admin panel CSS and JavaScript files now load only on StCR pages
* **Fix** Tooltips not showing up on System options page
* **Fix** Conflict with MailChimp for WP plugin (comment filter received echo instead of return which caused the issue)
* **Fix** Issue with select/deselect all on management page
* **Tweak** The MySQL requirements info on the system page now uses WordPress requirements
* **Tweak** The post author will no longer be notified of his/her own comments

= v190305 =

* **Fix** Issue with "Subscribe authors" functionality sending the emails to administrator instead of the post author

= v190214 =

* **Fix** String error calling the Curl Array.
* **Fix** wrong array definition that was breaking the site in some newer PHP versions.
* **Fix** error by calling `$wp_locale` that was not needed.
* **Fix** wrong label on option issue #467.
* **Fix** typo en help description issue #468.

= v190117 =

* **Fix** missing checkbox when the option **StCR Position** was set to **Yes**.
* **Fix** styles on admin notices.
* **Fix** filenames to match the correct menu name.
* **Fix** [# issue#431](https://github.com/stcr/subscribe-to-comments-reloaded/issues/431) and [issue#444](https://github.com/stcr/subscribe-to-comments-reloaded/issues/444).
* **Fix** warning message that was notifying the server when the Management URL was empty. Now the field must have a value. Props @breezynetworks on [WordPress Forum](https://wordpress.org/support/topic/php-warning-strpos-empty-needle/)
* **Fix** value of management page to get it on the event insteadof page load.
* **Add** translation for Subs Table, Add PHP error logger.
* **Add** Plugin information on Cards.
* **Add** the Phing build script to automate the deployment and testings.
* **Add** WebUI Popover library to display the help messages in a clear way.
* **Add** dropdown menu on the options tab to include the System menu.
* **Add** option to download the system report.
* **Add** functionality to create the system report via Ajax.
* **Add** cron to clean house the system report file.
* **Upgrade** the **Comment Form** Panel 2 options.
* **Upgrade** the **Management Page** panel.
* **Upgrade** the **Options** Panel.
* **Upgrade** the **Support** Panel.
* **Upgrade** the **StCR System** Menu.
* **Update** font awesome refrences.
* **Update** Admin Menus with Bootstrap.
* **Implement** Pagination using plugins and fix responsive layout of management page.
* **Implement** SASS for the CSS files.
* **Implement** a cache array for the menu options.
* **Remove** unecessary components from Composer.
* **Remove** double inclusion of Font Awesome.
* **Modify** Bower, Gulp and Phing files to implement WebUI Popover.
* **Refactor** the options saving process.
* **Refactor** code to move the functional saving options to the Utils Class.
* **Set** the Double Verification option to yes.
* **Create** array of options for improve support.
* **Sanitize** input and out of data preventing XSS. Code modification and suggestion by @jnorell.
* **Re Word** option to avoid missleading to new users. Props @padraigobeirn.
* **Move** the plugin core files to the folder **src** in order to implement `npm` and `bower` task managers.

= v180225 =

* **Fix** error when a user subscribe to a new post and the double opt-in was enable, preventing the double opt-in not sending the email message. [Issue#350](https://github.com/stcr/subscribe-to-comments-reloaded/issues/350).
* **Add** email and post id validation on the StCR backened.
* **Add** email, search and post id validation on the frontend.
* **Add** backened validation for input data (email) on the subscribe and request management pages.
* **Add** debug messages to improve support.
* **Add** feature to change the date format output on the management page for both the User page and Author. See [issue#345](https://github.com/stcr/subscribe-to-comments-reloaded/issues/345).
* **Remove** the inclusion of the plugin scripts with WP enqueue. This will load only the needed script on specific pages. Will remove request to the server to get scripts.

== Language Localization ==

Subscribe to Comments Reloaded can speak your language! If you want to provide a localized file in your
language, use the template files (.pot) you'll find inside the `langs` folder,
and [contact me](http://subscribe-reloaded.com/contact/) once your
localization is ready. Currently, we support the following languages:

* Danish - [Torben Bendixen](http://www.freelancekonsulenten.dk/)
* Dutch - [Martijn Chel](http://mcpnetwork.nl/),[Muratje](http://www.muromedia.nl/)
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
* Spanish - [TodoWordPress team](http://www.todowp.org/), [Juan Luis Perez](http://www.juanluperez.com/), [Iván Ridao Freitas](http://ivanrf.com/)
* Turkish - [MaD, Kali](http://www.dusunsel.com/)
* Hebrew - [Ahrale Shrem](http://atar4u.com/), [Eitan Caspi](http://fudie.net/)
* Hungarian - [László Tavaszi]
* Czech - [Daniel Král](http://www.danielkral.cz/)
* Persian - [Javad Hoseini-Nopendar](http://www.irannopendar.com/), [omid020](https://github.com/omid020)