=== Plugin Name ===
Contributors: dimadin
Donate link: http://example.com/
Tags: Gravatar, gravatar, avatar, avatars, comments, comment
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 0.94.1

Shows a message with link to Gravatar's signup page to commenters and/or users without gravatar.

== Description ==

This plugin shows a message with link to signup page of Gravatar (pre-filled with e-mail address) to commenters and/or users who don't have gravatar. 

Message can be shown to:

*   unregistered commenters when they leave text input field for e-mail address
*   registered commenters to whom their registered e-mail address is checked
*   registered users on their profile page, to whom their registered e-mail address is checked
*   users who fill registration form when they leave text input field for e-mail address

Options are fully customizable. See FAQ for more information.

This plugin is lightweight, it add only one field in database which is deleted if you uninstall plugin using WordPress' built-in feature for deletion of plugins. Also it will only load jQuery file to head of your page if it wasn't already loaded by theme or other plugin(s). Checks for gravatar are done via simple AJAX.
If you want to speed up your web site and save on bandwidth and server resources, it is recommended that you also install plugin [Use Google Libraries](http://jasonpenney.net/wordpress-plugins/use-google-libraries/).

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

Yes, you can add styles for message. Whole message is wraped with div with ID depending on case:
* `gse_tip_comment` for comment form
* `gse_tip_profile` for profile page
* `gde_tip_registration` for registration page

= Can I customize text of message? =

Yes, you can write any message you want, even use HTML tags you want. Note that you should leave link with URL placeholder if you want to show link to Gravatar's signup page pre-filled with user's e-mail address.

= Can I have different message for all cases? =

No.

= Can I translate plugin to language other than English? =

Yes, this plugin is fully internationalized, you can translate all text and link to locale version of Gravatar's site.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)
2. This is the second screen shot

== Changelog ==

= 0.94.1 =
* Fixing some grammar and spelling errors and changing several text strings.

= 0.94 =
* First alpha version in SVN.
