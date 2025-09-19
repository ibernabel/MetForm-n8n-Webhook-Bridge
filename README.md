# MetForm → n8n Webhook Bridge

A lightweight WordPress plugin that connects **MetForm form submissions** directly to **n8n workflows** in real-time.

## ✨ Features

- 🔄 **Real-time forwarding** - Form submissions sent immediately (no queues, no delays)
- 🔐 **Secure authentication** - Custom secret header validation
- ⚙️ **Easy configuration** - WordPress admin interface
- 🪵 **Comprehensive logging** - Track success and failures
- 🛡️ **Security first** - HTTPS enforcement, input sanitization
- ⚡ **Lightweight** - Minimal performance impact

## 📋 Requirements

- **WordPress** 5.0 or higher
- **MetForm** plugin (Free or Pro version)
- **n8n instance** with accessible webhook endpoint
- **HTTPS** webhook URL (required for security)

## 🚀 Quick Start

### 1. Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/metform-n8n-webhook-bridge/`
3. Activate the plugin through the WordPress admin

### 2. WordPress Configuration

1. Go to **Settings → MetForm → n8n**
2. Fill in the required settings:
   - **Webhook URL**: Your n8n webhook endpoint (must use HTTPS)
   - **Secret**: Strong password (minimum 12 characters)
   - **Enable Integration**: Check to activate
3. Click **Save Changes**

### 3. n8n Configuration

In your n8n webhook node, add this validation code:

```javascript
// Validate webhook secret
const receivedSecret = $request.headers['x-webhook-secret'];
const expectedSecret = 'YOUR_SECRET_HERE'; // Same as WordPress settings

if (receivedSecret !== expectedSecret) {
  $respond.status(401).json({error: 'Invalid secret'});
  return;
}

// Process the form data
const formData = $input.all();
return formData;
```

## 📊 Data Structure

The plugin sends this JSON payload to your n8n webhook:

```json
{
  "form_id": "123",
  "fields": {
    "field_name_1": "user_input_1",
    "field_name_2": "user_input_2"
  },
  "timestamp": "2025-09-19T10:30:00+00:00",
  "site_url": "https://yoursite.com"
}
```

## 🔧 Configuration Options

| Setting | Description | Required |
|---------|-------------|----------|
| **Enable Integration** | Turn webhook forwarding on/off | No |
| **Webhook URL** | Your n8n webhook endpoint (HTTPS only) | Yes |
| **Secret** | Authentication secret (min 12 chars) | Yes |

## 🐛 Troubleshooting

### Common Issues

**✗ "Invalid configuration" error**
- Check webhook URL uses HTTPS
- Ensure secret is at least 12 characters
- Verify n8n webhook is accessible

**✗ Forms not reaching n8n**
- Check WordPress error logs
- Verify webhook URL is correct
- Test webhook endpoint manually
- Ensure MetForm is active

**✗ "Unexpected response" error**
- Check n8n workflow is active
- Verify secret matches in both systems
- Review n8n execution logs

### Logging

Enable WordPress debug logging to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs at: `/wp-content/debug.log`

## 🔒 Security

This plugin implements several security measures:

- **HTTPS enforcement** for webhook URLs
- **Secret-based authentication** with minimum length requirements
- **Input sanitization** for all form data
- **SSL certificate verification** for webhook calls
- **No sensitive data storage** in plain text

## 🤝 Contributing

Found a bug or have a feature request? Please create an issue or submit a pull request.

## 📄 License

This plugin is released under the MIT license.

## 📞 Support

For support questions:
1. Check the troubleshooting section above
2. Review WordPress and n8n logs
3. Create an issue with detailed information

## 🔄 Changelog

### Version 2.0
- ✨ Added WordPress admin interface
- 🔐 Enhanced security with HTTPS enforcement
- 🪵 Improved logging and error handling
- 🏗️ Refactored to OOP structure
- ✅ Added configuration validation

### Version 1.0
- 🎉 Initial release
- 🔄 Basic webhook forwarding
- 🔐 Secret header authentication
