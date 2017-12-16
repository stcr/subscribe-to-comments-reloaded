=== Subscribe To Comments Reloaded ===
Author: reedyseth, camu
Contributors: reedyseth, coolmann
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=XF86X93FDCGYA&lc=US&item_name=Datasoft%20Engineering&item_number=DI%2dSTCR&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: comments, subscribe, subscribe to comments, subscribe to comments reloaded, email, email notification, subscriptions, commenting, reply, reply to comments, post notification, comment notification, automatic comment notification, email signup
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 170607
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Subscribe to Comments Reloaded allows commenters to sign up for e-mail notifications of subsequent replies. Don't miss any comment.

== Description ==
Subscribe to Comments Reloaded is a robust plugin that enables commenters to sign up for e-mail notification of subsequent entries. The plugin includes a full-featured subscription manager that your commenters can use to unsubscribe to certain posts or suspend all notifications. It solves most of the issues that affect Mark Jaquith's version, using the latest Wordpress features and functionality. Plus, allows administrators to enable a double opt-in mechanism, requiring users to confirm their subscription clicking on a link they will receive via email or even One Click Unsubscribe.

## Requirements
* Wordpress 4.0 or higher
* PHP 5.3.13 or higher
* MySQL 5.x or higher

## Main Features
* Does not modify Wordpress core tables
* Easily manage and search among your subscriptions
* Imports Mark Jaquith's Subscribe To Comments (and its clones) data
* Imports comments from Comment Reply Notification plugin
* Messages are fully customizable, no poEdit required (and you can use HTML!) with a Rich Text Editor - WYSIWYG
* Disable subscriptions for specific posts
* One Click Unsubscribe
* Compatible with [Fluency Admin](http://deanjrobinson.com/projects/fluency-admin/) and [QTranslate](http://wordpress.org/extend/plugins/qtranslate/)

== Installation ==


1. If you are using Subscribe To Comments by Mark Jaquith, disable it (no need to uninstall it, though)
2. Upload the entire folder and all the subfolders to your Wordpress plugins' folder. You can also use the downloaded ZIP file to upload it.
3. Activate it
5. Customize the Permalink value under Settings > Subscribe to Comments > Management Page > Management URL. It **must** reflect your permalinks' structure
5. If you don't see the checkbox to subscribe, you will have to manually edit your template, and add `<?php global $wp_subscribe_reloaded; if (isset($wp_subscribe_reloaded)){ $wp_subscribe_reloaded->stcr->subscribe_reloaded_show(); } ?>` somewhere in your `comments.php`
6. If you're upgrading from a previous version, please **make sure to deactivate/activate** StCR.

== Frequently Asked Questions ==

= Where can I give a Donation to support the plugin? =
Thank you to your contributions the plugin gets better, please go to this [link](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=XF86X93FDCGYA&lc=US&item_name=Datasoft%20Engineering&item_number=DI%2dSTCR&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted) to give a PayPal donation.

= Are there any video tutorials? =
Yeah, I have uploaded a few videos for the following topics:

1. Issues [Updating StCR via WordPress Update](https://youtu.be/Lb6cVx2bBU8)
2. Issues with StCR links see [StCR Clickable Links](https://youtu.be/eFW-2NIRzBA)
3. Issues with empty emails or management messages? see [StCR Mamagement Message](https://youtu.be/yRxOY8yq_cc)

= Why my notifications are not in HTML format? =
Don't worry, just go to the Options tab an set to Yes the **Enable HTML emails** option.

= How can I reset all the plugin options? =
There is a new feature called **Safely Uninstall** that allow you to delete the plugin using the WordPress plugin interface. If you have the option set to **Yes** everything but the subscriptions created by the plugin will be wipeout. So after you made sure that you have this option to **Yes** you can deactivate the plugin and the delete it. Now you have to install the plugin via WordPress or Upload the plugin `zip` file and activate it, after this step all your settings will be as default and your subscriptions will remain.

= What can I do if the **Safely Unistall** does not have any value? =
Just deactivate and activate the plugin and you are all set. The default value will be **Yes**.

= Aaargh! Were did all my subscriptions go? =
No panic. If you upgraded from 1.6 or earlier to 2.0+, you need to deactivate/activate StCR, in order to update the DB structure

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
`<?php global $wp_subscribe_reloaded; if (isset($wp_subscribe_reloaded)){ $wp_subscribe_reloaded->stcr->subscribe_reloaded_show(); } ?>`

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

== Upgrade Notice ==

= v170607 =
**Fix Critical Bug** This version fix a critical bug on fresh installation regarding a database table creation.

= v170428 =
**Fix issues** This version fix a few bug reported on GitHub and also includes an improvement on the management page. See the change log for more details.

= v160915 =
**Fix issues** This version fix the StCR position in the comment form. Small improvement in the Management Page.

= v160902 =

**Fix release; PLEASE UPGRADE IMMEDIATELY** This version fixes bugs regarding broken links and wrong management page assignments.

= v160831 =

**Major Release** On this version there have been a lot of patches and upgrades in the code, although there are many other issues to improve and fix this version will make your site and subscribers happy.

= v160115 =

**Subscription broken Fix; PLEASE UPGRADE IMMEDIATELY**. This fixes the broken links while trying to subscribe without commenting.

= v160106 =

This version is a mayor version change on StCR. It includes many changes and features like One Click Unsubscribe, A Rich editor for the notifications templates, Subscription Checkbox position, Huge code refactor just to mention a few.

= v150820 =

**Security Fix; PLEASE UPGRADE IMMEDIATELY**. v150820 fixes an XSS/CSRF vulnerability that was reported by a WordPress Plugin Repository moderator.

v150611 Fix the creation of the new table realated to the Google PII issue with AdWords, see v150422 Change log for details.

= v150611 =

v150611 Fix the creation of the new table realated to the Google PII issue with AdWords, see v150422 Change log for details.

= v150422 =

**Security Fix; PLEASE UPGRADE IMMEDIATELY** Google PII issue with AdWords. Protect user email Address and uses an encrypted key instead on URL. Several issues are fix, see the change log.

= v150207 =

Improvements on the links security. Now you get a Unique Key for your site. Check the Options Panel.

= v141103 =

**Broken links and settings issue** Please upgrade to fix the URL creation on the Request Management link and to save the settings values correctly.

= v141025 =

v1410124 Fixed several issues reported on the support forum like broken links, raw HTML on the messages, clean user interface with buttons not needed. See the change log for details.

= v140220 =

**Security Fix; PLEASE UPGRADE IMMEDIATELY**. v140219 fixes an XSS/CSRF vulnerability that was discovered by Tom Adams and reported by a WordPress Plugin Repository moderator.

== Changelog ==

= v170607 =

* **Fix Critical Bug** This version fix a critical bug on fresh installation regarding a database table creation.
* **Add** Option to control the inclusion of the style Font Awesome [issue#344](https://github.com/stcr/subscribe-to-comments-reloaded/issues/344).

= v170428 =

* **Fix** broken code while using BBCode plugin. This cause the select all subscriptions to be broken [issue#56](https://github.com/stcr/subscribe-to-comments-reloaded/issues/56).
* **Fix** wrong link on the StCR column under the comments list. [Issue#328](https://github.com/stcr/subscribe-to-comments-reloaded/issues/328)
* **Fix** double check confirmation link with missing srek key. [issue#329 & issue#305](https://github.com/stcr/subscribe-to-comments-reloaded/issues/329).
* **Fix** error that the plugin was no delivering notifications when a user A was subscribe and user B was not subscribe and replying to user A message. [issue#324](https://github.com/stcr/subscribe-to-comments-reloaded/issues/324).
* **Fix** confusing options when trying to select all the subscriptions or invert on the management page. [issue#339](https://github.com/stcr/subscribe-to-comments-reloaded/issues/339).
* **Fix** non translated text on the new Management Page.
* **Add** RC status to the translate array. [Issue#330](https://github.com/stcr/subscribe-to-comments-reloaded/issues/330).
* **Add** options on the *StCR Menu* to control the usage of the log file. **Add** option to auto purge the log file or disable the loggin. [Issue#312](https://github.com/stcr/subscribe-to-comments-reloaded/issues/312).
* **Add** Font Awesome to have a nicer UI.
* **Add** option to disable the subscription/dropdown from the comment from. [issue#183](https://github.com/stcr/subscribe-to-comments-reloaded/issues/183).
* **Add** the permalink to the confirmation page so that the user can return to the post where the requested to be subscribed.
* **Improve** the UI of the Management Page to be more friendly. Delete the leyend and instead add the complete subs status.
* **New feature** to include the WordPress Gravatar on the notification message. Suggested by @lolobu on [issue#188](https://github.com/stcr/subscribe-to-comments-reloaded/issues/188).
* **New feature**. **Add** a back to post button to the Author/Admin page and the User page. This will allow the user to return to where they were. [issue#254](https://github.com/stcr/subscribe-to-comments-reloaded/issues/254).
* **Remove** the Activate status from the dropdown menu of the management page, this value has no purpose on this version.
* **Improve** Auto fill the email address to the request management page and the subscribe without commenting page when the user is logged in in WordPress. [issue#325](https://github.com/stcr/subscribe-to-comments-reloaded/issues/325).
* **Improve** the validation of $sre and $srek keys on the `wp_subscribe_reloaded\subscribe_reloaded_manage()` to avoid sending null values to the logger and the get the correct email information.

= v160915 =

* **Fix** StCR checkbox position. Some WordPress themes does not use the latest WordPress standard and therefore the hook `comment_form_submit_field` does not work, as a result the checkbox is not visible. To force the checkbox visibility a new options called **a** was added in order to make the checkbox visible. See [issue#260](https://github.com/stcr/subscribe-to-comments-reloaded/issues/260)
* **Improve** Email validation for empty values and using a regex.
* **Improve** StCR update detection. Using the WordPress **Shiny Update** does not trigger the activate hook correctly.
* **Change** Radio buttons in the management page for a dropdown menu. Props[issue#247](https://github.com/stcr/subscribe-to-comments-reloaded/issues/247#issuecomment-242662558)

= v160902 =

* **Fix** Message that was refering to a key expire, also fix wrong See [issue#250](https://github.com/stcr/subscribe-to-comments-reloaded/issues/250)
* **Fix** Bug that was allowing a user that was logged in to manage another user subscription by knowing his/her email address.
* **Fix** Submition of management request with empty email. Now the request will be performed with an email.

= v160831 =

* **New Feature** Add new option to set the Reply To email address. This will help the subscribers to use the Reply option in their email agents.
* **New Feature** Improve the Admin Menu for StCR. Replace the StCR menu on the Settings Menu for a new Menu with sub menus for the pages.
* **New Feature** Safely Uninstall option to Delete the plugin without loosing your subscriptions. You can use this option also for reset all the settings, see the FAQ.
* **New Feature** Now the WordPress Authors can use the **Subscribe authors** option to autor subscribe to a Custom Post Type. [issue#126](https://github.com/stcr/subscribe-to-comments-reloaded/issues/126)
* **Info** The version 160831 has been tested down until PHP 5.3.13 and up to PHP 7.0. See also [issue#238](https://github.com/stcr/subscribe-to-comments-reloaded/issues/238)
* **Fix** email headers and new headers: Reply-To, To, and Subject. Some notification message where not deliver in public accounts like Gmail, AOL and Hotmail due to broken email headers.
* **Fix** links duplications on notification messages. [issue#198](https://github.com/stcr/subscribe-to-comments-reloaded/issues/198) and [issue#200](https://github.com/stcr/subscribe-to-comments-reloaded/issues/200)
* **Fix** the position of StCR box to be above the submit button by using the WordPress way, this is the way it should be. [issue#196](https://github.com/stcr/subscribe-to-comments-reloaded/issues/196)
* **Fix** hard code table name `wp_option` for the `$wpdb->options` way. Issue "SQL Error: 'wp_options' doesn't exist" [issue#197](https://github.com/stcr/subscribe-to-comments-reloaded/issues/197)
**Improve** Message notification, now the **Management Page message** is different than the **Management Email message**. On the **Management Page message** you only notify the author of the request and in the **Management Email message** you notify of the request and you can include the management link. [issue#247](https://github.com/stcr/subscribe-to-comments-reloaded/issues/247#issuecomment-242662558)
* **Improve** the subscribers list.
* **Improve** RTL support.
* **Improve** the support for RTL languages on the `Manage Subscriptions` admin page.
* **Update** some translations on the Persian file. [issue#191](https://github.com/stcr/subscribe-to-comments-reloaded/issues/191)
* **Add** Link on the Mass Update panel with instructions to help the user know what is for.
* **Add** the wp_editor to the comment form panel instead of only the TinyMCE. [issue#2017](https://github.com/stcr/subscribe-to-comments-reloaded/issues/207)
* **Add** descriptive statuses on the Subscribers list.
* **Add** subscribers unique key on the email as a title attribute.
* **Add** Unique Key expiration when the unique key is regenerated by the admin. This will fix also broken links on the email notifications.
* **Remove** the anchor wrappers on the request-management-link.php
* **Change** TinyMCE editor for wp_editor. [issue#2017](https://github.com/stcr/subscribe-to-comments-reloaded/issues/207)
* **Change** the radio buttons at the bottom of the subscriptions page for a select/combobox menu.
* **Remove** option to move subscription box on the options tabs. [issue#196](https://github.com/stcr/subscribe-to-comments-reloaded/issues/196)

= Warning =
**StCR version 160106 and above require at least PHP 5.3.X, so if you have a lower version your site might break, Read more** [here](https://wordpress.org/support/topic/fatal-error-upon-updating?replies=43) and [here](https://github.com/stcr/subscribe-to-comments-reloaded/issues/238#issuecomment-240486395)

= v160115 =

* **Fix** Error while calling the option to subscribe without commenting.
* **Fix** Error while deleting a subscription on the management page.

= v160106 =

* **Fix** The correct calling to the update function when a new version is available.
* **Fix** Minor string bugs, In the code there is a new line before closing ". So, the text has several \t and it is not getting the spanhis translations submitted by @IvanRF.
* **Fix** HTML input markup by removing ending slash on the input tags. See [issue#106](https://github.com/stcr/subscribe-to-comments-reloaded/issues/106).
* **Fix** Unique Key is empty after update [issue#88](https://github.com/stcr/subscribe-to-comments-reloaded/issues/88). Reset the unique key if exist by deleting it and add it again. See [fc31da](https://github.com/stcr/subscribe-to-comments-reloaded/commit/fc31dae41a4513ee269f3a2daaad877045e9f25f)
* **Fix** A critical issue on upgrade. Fix Upgrade routine since the activation hooks was not triggering on the upgrade process.
* **Fix** error on Manage Subscription Page. Change the function get_subscriber_key( $email = null) to return the unique key of the user instead of generate it.
* **Fix** Style on the generate key button.
* **Add** A donate Panel so that you can donate to the plugin and help keeping the support active.
* **Add** Ritch editor to admin page. Add a wysiwyg editor to the plugin to boost the HTML email. Please disable the option `HTMLify links in emails` to work correctly with the links.
* **Add** The class clearFix to clear any floating style. Implement this class in the menu tabs to fix an issue with Comment Mail. See issue#158.
* **Add** Scripts and Style to manage the plugin with jQuery.
* **Add** French language file with correction by Jean-Michel Meyer(@Li-An).
* **Add** Persian language, submitted  by Javad Hoseini-Nopendar.
* **Add** Mail messages localization support for WPML by @IvanRF.
* **Add** option to create a default template on the Notification tab of the settings.
* **Move** The subscription Checkbox above the submit button, a new option is added to enable/disable the Comment Box Position, it could be above the submit button or after. See [issue#118](https://github.com/stcr/subscribe-to-comments-reloaded/issues/118).
* **Update** Core code of StCR.
* **Update** Optimizing get_option calls. Change the option autoload to yes so that the values are store in the object cache for a faster load. See [issue#86](https://github.com/stcr/subscribe-to-comments-reloaded/issues/86)
* **Update** Panel Messages. Add new Support panel and move the support information from panel6.php to panel7.php on the StCR Settings.
* **Update** transalation references on new German Translation file provided by @maffi91 and @konus1. See issue#114 and [issue#135](https://github.com/stcr/subscribe-to-comments-reloaded/issues/135).
* **Update** Various notice style and spelling improvements. See [issue#135](https://github.com/stcr/subscribe-to-comments-reloaded/issues/135)

= v150820 =

**Security Fix** Fix an XSS/CSRF vulnerability that was reported by a WordPress Plugin Repository moderator.

= v150611 =

* **Fix** The creation of the new table realated to the Google PII issue with AdWords, see [issue#100](https://github.com/stcr/subscribe-to-comments-reloaded/issues/100)
* **Fix** The manage subscription link broke due to a wrong SRE key generation, see [issue#102](https://github.com/stcr/subscribe-to-comments-reloaded/issues/102)
* **Add** Fixed French translation(thanks to Jean-Michel Meyer)

= v150422 =

* **Fix** Google PII complaint. See [issue#79](https://github.com/stcr/subscribe-to-comments-reloaded/issues/79) on GitHub.
* **Fix** The ability to manage any subscription is remove, the manage link will only appear on the subscriber email address. [issue#81](https://github.com/stcr/subscribe-to-comments-reloaded/issues/81).
* **Fix** Fix wrong html markup on the advance subscription dropdown.
* **Fix** Subscription List filter using the "start with" option on the Manage Subscription Panel. [issue#82](https://github.com/stcr/subscribe-to-comments-reloaded/issues/82)
* **Fix** HTML email label for for screen readers. reported on [issue#76](https://github.com/stcr/subscribe-to-comments-reloaded/issues/76).
* **Fix** Database information with correct encoding. A new routine to clean the database information encoding. This was outputting raw HTML.
* **New Feature** Update subscription status to 'All Comments' on user Management Page.
* **Add** New table to store every subscriber email.
* **Add** Czech translation file.
* **Add** Hungarian translation file.
* **Add** Hebrew translation file.

= v150207 =

* **Fix** The output link for the manage subscriptions.
* **Fix** Display of URL to use escape characters.
* **New Feature** a Unique Key to the plugin. This Key will help to prevent spam bots to hijack your links.
* **Add** Plugin GitHub link for bug reporting. Check the "You can Help" panel.
* **See** the commit history on (GitHub)[https://github.com/stcr/subscribe-to-comments-reloaded]

= v141103 =

* **Fix** URL generation for the Request Management link.
* **Fix** Saving of settings values. Settings with a single quote was not saving correcting. Every option with a single quote was broken, after update please save the settings on every tab where you have single quotes.

= v141025 =
* **Fix** Post variable missing on request_management_link.php.
* **Change** the helper class for the function esc_attr( $_value ) to safety add the URL.
* **Fix** data overflow when the comment reply column is not available and when the plugin is activated.
* **Fix** the raw HTML input on the messages. The messages where encoding twice, the fix is on index.php
* **Fix** broken link when Virtual Management Page is disabled.
* **Remove** bold, italic, link and image buttons, currently they don't work, right now this feature is not supported for the plugin.
* **Update** Virtual Management link, old one was broken. The new link points to the the Virtual Management Page documentation.

= v140515 =

* **New Feature** New import routine for Comment Reply Notification plugin. If subscription data is found for Comment Reply Notification, StCR will import those upon activation. Data is only imported if there is no existing StCR subscription data.
* **Bug Fix** Fixed Raw HTML notification. When the send HTML email was enable the message was sent with raw HTML. **Important** After you update to this version go to the Notification panel and click the `Save Changes` button to update your HTML message.
* **Change** `mysql_query() and mysql_query_row()` Deprecated functions for the WordPress `get_comment_author_email()`. Issues with PHP 5.5.x.

= v140220 =

**Bug Fix**. Fixes an encoding bug that broke HTML output after patching XSS vulnerability. If you started seeing raw HTML output at the bottom of your comment forms after upgrading to v140219, this update should fix that.

= v140219 =

* **Security Fix; PLEASE UPGRADE IMMEDIATELY**. Fixes XSS/CSRF vulnerability that was discovered by Tom Adams and reported by a WordPress Plugin Repository moderator.
* **Translations**. Updated French translation (thanks to Jean-Michel MEYER).
* Improvements to translation support (thanks to Carlos Alberto Lopez Perez).
* Add trailing slash to comment-subscriptions page to avoid unnecessary redirections (thanks to Carlos Alberto Lopez Perez).

= v140204 =

* **New Feature**. There is a new Option that Sets the default Subscription Type when the Chechbox 'Checked by default' is enable *Settings -> Subscribe to Comments -> Comment Form -> Default Checkbox Value*.
* Corrected reference to the language translation files. If you find something fuzzy please open a Issue on GitHub <https://github.com/stcr/subscribe-to-comments-reloaded/issues/new?title=Bug%20Report:%20%3Cshort%20description%3E&labels=bug>

= v140129 =

* **Bug Fix**. Fixed `Notice: Undefined variable: post_id` that was sometimes causing issues with creating a new subscription when WordPres Debug mode was enabled. See: <https://github.com/stcr/subscribe-to-comments-reloaded/issues/2>
* Added missing `.mo` files for translations in `lang/`. See: <https://github.com/stcr/subscribe-to-comments-reloaded/issues/13>

= v140128 =

* **New Feature**. There is now an option to BCC the admin on all Notifications. This is very useful when troubleshooting email delivery issues.
* **New Option**. There is a new 'HTMLify links in emails' Option. When using HTML emails for messages you can now choose to have StCR automatically HTMLify the links for you (*Settings -> Subscribe to Comments -> Options -> HTMLify links in emails*). You can, of course, leave this option disabled and add your own HTML to the messages if you prefer.
* **New Option**. There is a new 'default subscription type' Option. If you're using Advanced subscriptions, you can now specify the Advanced default subscription type ("None", "All new comments", or "Replies to this comment") in *Settings -> Subscribe to Comments -> Comment Form -> Advanced default*. This will be the default option shown on the comment form.
* **Bug Fix**. Paragraph tags are now properly added to the comment content when sending HTML emails with `[comment_content]`
* **Bug Fix**. Partial fix for the broken Subscribe to Replies Only feature. The Replies Only feature has not been working as intended. Instead of only receiving notificaitons for replies to their own comment, subscribers were receiving notifications for all new comments on the post. This fix makes sure they only receiving replies to their own comment thread.
* **Bug Fix**. Fix duplicate `MIME-Version` header bug resulting in unsent emails. Fixes a bug where using StCR with other plugins, like WP-Mail-SMTP, results in a quiet duplicate header error. `wp_mail()` already takes care of setting the `MIME-Version` header so this doesn't need to be done again.
* **Bug Fix**. Fixed `Fatal Error: Cannot redeclare class Helper` when visiting the `[subscribe_link]`. See also: <http://wordpress.org/support/topic/bug-fatal-error-in-classeshelperclassphp>
* New import routine for WP Comment Subscriptions plugin. If subscription data and options are found for WP Comment Subscriptions, StCR will import those upon activation. Options and data are only imported if there is no existing StCR subscription data.
* New admin notices to improve messaging and indicate when data is imported from an existing plugin.
* New `stcr_confirmation_email_message` hook to modify the message that is sent to confirm a subscription. (Thanks to ziofix!)
* New `stcr_notify_user_message` hook to modify the notification message that is sent to a user. (Thanks to ziofix!)
* New plugin versioning format of YYMMDD.
* Plugin development is now actively happening over at the new GitHub Repository for Subscribe to Comments Reloaded. If you have a bug to report or want to make a feature request, please post a new Issue over at GitHub. If you're a programmer, you're welcome to submit a Pull Request! See: <https://github.com/stcr/subscribe-to-comments-reloaded>
* Added WPML language configuration file.
* Added Raam Dev (`raamdev`) to the contributors list.

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
* Added: option to disable the virtual management page, for those [having problems](http://behstant.com/subscribe-reloaded/realMgnPage.php) with their theme
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