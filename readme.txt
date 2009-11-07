=== Plugin Name ===
Contributors: dimadin
Tags: Gravatar, gravatar, gravatars, avatar, avatars, comment, comments
Requires at least: 2.8
Tested up to: 2.9-rare
Stable tag: 1.0

Shows a message with link to Gravatar's signup page to commenters and/or users without gravatar.

== Description ==

This plugin shows a message with link to signup page of Gravatar (pre-filled with e-mail address) to commenters and/or users who don't have gravatar. 

Message can be shown to:

*   unregistered commenters when they leave text input field for e-mail address
*   registered commenters to whom their registered e-mail address is checked
*   registered users on their profile page, to whom their registered e-mail address is checked
*   users who fill registration form when they leave text input field for e-mail address

Options are fully customizable. See FAQ for more information.

This plugin is lightweight, it adds only one field in database which is deleted if you uninstall plugin using WordPress' built-in feature for deletion of plugins. Also it will only load jQuery file to head of your page if it wasn't already loaded by theme or other plugin(s). Checks for gravatar are done via simple AJAX.
If you want to speed up your web site and save on bandwidth and server resources, it is recommended that you also install plugin [Use Google Libraries](http://jasonpenney.net/wordpress-plugins/use-google-libraries/) which will load jQuery file from [Google AJAX Libraries](http://code.google.com/apis/ajaxlibs/).

In order to plugin works, it needs to be on server with PHP 5 and on WordPress 2.8 or above.

== Installation ==

1. Upload `gravatar-signup-encouragement` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You can change default options on 'Discussion Settings' page

== Frequently Asked Questions ==

= Do I need to show message in all cases (comments, profile, registration)? =

No, you can select where you want to show message, you can select all cases or just one.

= Can I choose where on page to show message? =

Yes, you can choose below which elements on page to show message. There are several elements already available to choose for all cases and you can alternatively add custom element by providing it's id. Since this plugin uses [jQuery selectors](http://docs.jquery.com/Selectors) to find element, you can add even more advanced filters for selecting. Note that all selectors start with # . Also be aware that display of message may not look good with your theme.

= Can I customize style of message? =

Yes, you can add styles for message. Whole message is wrapped with div with ID depending on case:

*   `gse_comments_message` for comment form
*   `gse_profile_message` for profile page
*   `gse_registration_message` for registration page

= Can I customize text of message? =

Yes, you can write any message you want, even use HTML tags you want. Note that you should leave link with URL placeholder if you want to show link to Gravatar's signup page pre-filled with user's e-mail address.

= Can I have different message for all cases? =

No.

= Can I translate plugin to language other than English? =

Yes, this plugin is fully internationalized, you can translate all text and link to locale version of Gravatar's site. You can find .pot file in root folder and you should place your translation in`translations` folder. Please make a [contact](http://blog.milandinic.com/contact/) for sending your translation so that it can be included in official realease.

Currently, plugin includes following translations:

* [Serbian](http://www.milandinic.com/2009/11/07/podstaknite-korisiscenje-gravatara/), by author himself
* [Danish](http://wordpress.blogos.dk/s%C3%B8g-efter-downloads/?did=224), thanks to GeorgWP

= Will this plugin enable use og Gravatar's API for managing avatars directly from WordPress installation? =

No, this plugin will never add that feature since author of this plugin is against managing of account on Gravatar from remote site.

== Screenshots ==

1. Settings form with all expanded options
2. Message shown to unregistered commenter on default theme with default settings
3. Message shown to registered commenter on default theme with default settings
4. Message shown on profile page with option to show below “About Yourself” header
5. Message shown on registration page with default settings

== Changelog ==

= 1.0 =
* Moved URL localization and message preparation to function so that URL localization could work and to improve performance, as per [suggestion](http://groups.google.com/group/wp-hackers/browse_thread/thread/4fdc895360c3b087#) from Otto
* Added load_plugin_textdomain function in register_activation_hook so that default message can be localized on activation, as per confirmation from Otto
* Fixed issue with showing of message on registration page when user change e-mail address to one that does have a gravatar
* Added Danish translation; thanks to [GeorgWP](http://wordpress.org/support/topic/326328)
* Moved .pot file to root folder
* Several small cleanups and moves of code

= 0.94.8 =
* Fixed issue with showing of message to unregistered commenters who changed e-mail address to one that does have a gravatar

= 0.94.2 =
* Updated plugin's meta-data

= 0.94.1 =
* Fixed some grammar and spelling errors and changing several text strings.

= 0.94 =
* First alpha version in SVN.
