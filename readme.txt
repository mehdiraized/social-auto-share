=== Social Auto Share ===
Contributors: mehdiraized
Tags: social media, telegram, telegram bot, auto share to social
Requires at least: 5.0
Tested up to: 6.6.2
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin for automatically sharing content to social media platforms.

== Description ==

Social Auto Share helps you automatically share your WordPress content to social media platforms. Currently supporting Telegram with plans for more platforms.

= Key Features =

* Automatic content sharing to Telegram (extensible to other platforms)
* Support for various content types (posts, products, etc.)
* Customizable message templates per platform
* Content filtering based on:
  * Content type
  * Categories
  * Minimum word count
* Featured image sharing capability
* Markdown and HTML format support
* Fully multilingual (includes Persian translation)
* Modular and extensible design

= Message Template Variables =

Use these variables in your message templates:

* {title} - Content title
* {excerpt} - Content excerpt
* {url} - Content URL
* {author} - Author name
* {date} - Publication date
* {categories} - Categories
* {tags} - Tags

= Requirements =

* PHP 7.4 or higher
* WordPress 5.8 or higher
* Required PHP extensions:
  * curl
  * json
  * mbstring

= Multilingual Support =

The plugin includes translations for:
* English (default)
* Persian (fa_IR)

= Support and Documentation =

* For detailed documentation, visit [Persian Documentation](https://github.com/yourusername/social-auto-share/blob/main/readme-fa.md)
* For support and feedback, visit our [support page](https://mehd.ir)

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings -> Social Share to configure the plugin

= Telegram Setup =

1. Create a Telegram bot through [@BotFather](https://t.me/BotFather)
2. Get the bot token
3. Add the bot to your channel and make it an administrator
4. Enter the channel ID (e.g., @channelname) in the settings
5. Configure your message template
6. Optionally enable featured image sharing

== Frequently Asked Questions ==

= Which social media platforms are supported? =

Currently, the plugin supports Telegram. Support for Instagram, Twitter, and LinkedIn is planned for future releases.

= Can I customize the sharing message format? =

Yes, you can fully customize the message template using variables like {title}, {excerpt}, {url}, etc.

= Does it support custom post types? =

Yes, you can enable sharing for any public post type in your WordPress installation.

= Is it compatible with multilingual websites? =

Yes, the plugin is fully translatable and comes with English and Persian translations.

== Screenshots ==

1. Main settings page showing platform configuration options
2. Telegram-specific settings and setup instructions
3. Content type settings and filters
4. Message template customization interface on Telegram App

== Changelog ==

= 1.0.0 =
* Initial release
* Telegram platform support
* Basic post type sharing
* Multilingual support (English & Persian)

== Upgrade Notice ==

= 1.0.0 =
Initial release of Social Auto Share plugin.

== Donate ==

If you find this plugin useful, please consider [buying me a coffee](https://www.buymeacoffee.com/mehdiraized). Your support helps maintain and improve the plugin.