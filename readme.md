# Social Auto Share

**Contributors:** mehdiraized  
**Tags:** social media, telegram, telegram bot, auto share to social  
**Requires at least:** 5.0  
**Tested up to:** 6.6.2  
**Stable tag:** 1.0.0  
**Requires PHP:** 7.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin for automatically sharing content to social media platforms.

[🇮🇷 Persian Documentation (مستندات فارسی)](readme-fa.md)

## Features

- Automatic content sharing to Telegram (extensible to other platforms)
- Support for various content types (posts, products, etc.)
- Customizable message templates per platform
- Content filtering based on:
  - Content type
  - Categories
  - Minimum word count
- Featured image sharing capability
- Markdown and HTML format support
- Fully multilingual (includes Persian translation)
- Modular and extensible design

## Installation

1. Upload the plugin folder to the `wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the plugin settings page and configure the required settings

## Configuration

### Telegram Settings:

1. Create a Telegram bot through [@BotFather](https://t.me/BotFather)
2. Get the bot token
3. Add the bot to your channel and make it an administrator
4. Enter the channel ID (e.g., @channelname) in the settings
5. Configure your message template
6. Optionally enable featured image sharing

### Content Settings:

1. Select which content types you want to share
2. Choose or exclude specific categories
3. Set minimum word count if desired
4. Configure excerpt length
5. Decide whether to share content updates

## Message Template Variables

The following variables can be used in message templates:

- `{title}`: Content title
- `{excerpt}`: Content excerpt
- `{url}`: Content URL
- `{author}`: Author name
- `{date}`: Publication date
- `{categories}`: Categories
- `{tags}`: Tags

## Development

### Adding a New Platform

1. Create a new class in the `platforms` directory extending `Social_Platform`:

```php
class Social_Platform_Instagram extends Social_Platform {
    public function get_id() {
        return 'instagram';
    }

    public function get_name() {
        return __('Instagram', 'social-auto-share');
    }

    // Implement other required methods...
}
```

2. Register the platform in the main plugin class:

```php
add_filter('wp_social_auto_share_platforms', function($platforms) {
    $platforms['instagram'] = new Social_Platform_Instagram();
    return $platforms;
});
```

### Adding a New Content Type

1. Create a new class in the `content-types` directory extending `Content_Type`:

```php
class Content_Type_Product extends Content_Type {
    public function get_id() {
        return 'product';
    }

    public function get_name() {
        return __('Products', 'social-auto-share');
    }

    // Implement other required methods...
}
```

2. Register the content type in the main plugin class:

```php
add_filter('wp_social_auto_share_content_types', function($content_types) {
    $content_types['product'] = new Content_Type_Product();
    return $content_types;
});
```

## WordPress Filters

The plugin provides the following filters:

- `wp_social_auto_share_platforms`: Add/remove platforms
- `wp_social_auto_share_content_types`: Add/remove content types
- `wp_social_auto_share_post_content_data`: Modify content data before sharing
- `wp_social_auto_share_enabled_platforms`: Control active platforms
- `wp_social_auto_share_should_share`: Control whether a content should be shared

## Advanced Customization

### Custom Message Formatting

You can customize how messages are formatted for each platform:

```php
add_filter('wp_social_auto_share_post_content_data', function($content_data, $post) {
    // Modify content data
    $content_data['title'] = '[' . get_post_type($post) . '] ' . $content_data['title'];
    return $content_data;
}, 10, 2);
```

### Platform-Specific Settings

Each platform can have its own settings section:

```php
add_filter('wp_social_auto_share_platform_settings', function($settings, $platform_id) {
    if ($platform_id === 'your_platform') {
        $settings['custom_option'] = 'default_value';
    }
    return $settings;
}, 10, 2);
```

## Requirements

- PHP 7.4 or higher
- WordPress 5.8 or higher
- Required PHP extensions:
  - curl
  - json
  - mbstring

## Multilingual Support

The plugin supports multiple languages by default:

- English (default)
- Persian (fa_IR)

To add a new language:

1. Place translation files in the `languages` directory
2. Use the naming format `social-auto-share-{locale}.po`
3. Generate the corresponding `.mo` file

## Contributing

We welcome contributions to this plugin! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Changelog

### 1.0.0

- Initial release
- Telegram platform support
- Basic post type sharing
- Multilingual support

## Roadmap

Future features planned:

- Instagram integration
- Twitter integration
- LinkedIn support
- Scheduling capabilities
- Analytics dashboard
- Bulk sharing tools
- API for custom integrations

---

## Support and Feedback

For support and feedback, please visit our [support page](https://mehd.ir). We value your feedback and suggestions for improving the plugin.

## Donate

If you find this plugin useful, please consider supporting its development by [buying me a coffee](https://www.buymeacoffee.com/mehdiraized). Your support helps cover the costs of maintaining and improving the plugin, ensuring it remains free and accessible for everyone. Thank you!

## License

This plugin is licensed under the GPLv2 or later. For more details, visit [GPL License](https://www.gnu.org/licenses/gpl-2.0.html).
