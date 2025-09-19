# Technical Documentation

## Architecture Overview

### Plugin Structure

```
MetFormN8nWebhook (Main Class)
├── Configuration Management
│   ├── WordPress Options API
│   ├── Input Validation & Sanitization
│   └── Admin Settings Interface
├── Webhook Processing
│   ├── MetForm Hook Integration
│   ├── Payload Preparation
│   └── HTTP Request Handling
├── Security Layer
│   ├── HTTPS Enforcement
│   ├── Secret Validation
│   └── Input Sanitization
└── Logging System
    ├── Error Logging
    ├── Success Logging (Debug Mode)
    └── Context Preservation
```

## Core Components

### 1. Class Constants

```php
private const OPTION_WEBHOOK_URL = 'metform_n8n_webhook_url';
private const OPTION_SECRET = 'metform_n8n_secret';
private const OPTION_ENABLED = 'metform_n8n_enabled';
```

Used for consistent option naming and easy maintenance.

### 2. Initialization Flow

```
WordPress Init
    ↓
Check MetForm Active
    ↓
Register Hooks & Admin Menu
    ↓
Ready for Form Submissions
```

### 3. Form Submission Processing

```
MetForm Submission
    ↓
Integration Enabled Check
    ↓
Configuration Validation
    ↓
Payload Preparation
    ↓
HTTP Request to n8n
    ↓
Response Handling & Logging
```

## Security Implementation

### Input Validation

| Input Type | Validation Method | Security Measures |
|------------|------------------|-------------------|
| Webhook URL | `filter_var()` + HTTPS check | HTTPS enforcement, URL format validation |
| Secret | String length + sanitization | Minimum 12 characters, special char support |
| Form Data | Recursive sanitization | `sanitize_textarea_field()` for all values |

### Authentication Flow

```
WordPress → n8n Request
    ↓
Custom Header: X-Webhook-Secret
    ↓
n8n Validation (User Implemented)
    ↓
Process or Reject
```

## Database Schema

### WordPress Options

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `metform_n8n_webhook_url` | string | HTTPS webhook endpoint | empty |
| `metform_n8n_secret` | string | Authentication secret | empty |
| `metform_n8n_enabled` | boolean | Integration toggle | false |

## API Reference

### Main Methods

#### `handle_form_submission($form_id, $response)`
**Purpose**: Process MetForm submission and forward to n8n
**Parameters**:
- `$form_id` (int): MetForm form identifier
- `$response` (array): Form submission data

**Flow**:
1. Check if integration is enabled
2. Validate configuration
3. Prepare payload
4. Send webhook request
5. Handle response/errors

#### `validate_configuration($webhook_url, $secret)`
**Purpose**: Validate webhook settings before sending
**Returns**: `bool` - Configuration validity
**Validation Rules**:
- URL must be valid format
- URL must use HTTPS
- Secret minimum 12 characters

#### `prepare_payload($form_id, $response)`
**Purpose**: Structure data for n8n consumption
**Returns**: `array` - Formatted payload

**Payload Structure**:
```php
[
    'form_id' => sanitized_form_id,
    'fields' => sanitized_form_data,
    'timestamp' => ISO_8601_timestamp,
    'site_url' => wordpress_site_url
]
```

## Hooks & Filters

### WordPress Hooks Used

| Hook | Priority | Parameters | Purpose |
|------|----------|------------|---------|
| `init` | 10 | none | Initialize plugin functionality |
| `metform_after_submit` | 10 | `$form_id, $response` | Capture form submissions |
| `admin_menu` | 10 | none | Add settings page |
| `admin_init` | 10 | none | Register settings |

### Custom Hooks (Future Extension Points)

```php
// Before sending webhook (not implemented yet)
do_action('metform_n8n_before_webhook', $payload, $webhook_url);

// After webhook response (not implemented yet)
do_action('metform_n8n_after_webhook', $response, $payload);

// Filter payload before sending (not implemented yet)
$payload = apply_filters('metform_n8n_payload', $payload, $form_id);
```

## Error Handling

### Error Types

1. **Configuration Errors**
   - Missing webhook URL
   - Invalid URL format
   - Non-HTTPS URLs
   - Weak secrets

2. **Request Errors**
   - Network timeouts
   - SSL certificate issues
   - DNS resolution failures
   - Connection refused

3. **Response Errors**
   - HTTP 4xx/5xx status codes
   - Invalid response format
   - n8n workflow errors

### Logging Format

```
[MetForm→n8n] TYPE: message | Context: {json_context}
```

**Examples**:
```
[MetForm→n8n] ERROR: Invalid configuration: missing webhook URL
[MetForm→n8n] ERROR: Webhook request failed: cURL error 28: Operation timed out
[MetForm→n8n] SUCCESS: Webhook sent successfully | Context: {"form_id":"123"}
```

## Performance Considerations

### Current Implementation
- **Memory Usage**: Minimal (single class, no large data structures)
- **Execution Time**: ~0.5-2 seconds per form submission (network dependent)
- **Database Queries**: 3 queries per submission (option reads)
- **HTTP Timeout**: 30 seconds (configurable)

### Optimization Opportunities
1. **Option Caching**: Cache configuration options in memory
2. **Async Processing**: Queue webhook calls for background processing
3. **Connection Pooling**: Reuse HTTP connections (WordPress limitation)

## Testing

### Unit Testing Structure (Recommended)

```
tests/
├── TestMetFormN8nWebhook.php
├── TestConfigurationValidation.php
├── TestPayloadPreparation.php
├── TestWebhookSending.php
└── TestErrorHandling.php
```

### Manual Testing Checklist

- [ ] Plugin activation/deactivation
- [ ] Settings page accessibility
- [ ] Configuration validation (invalid URLs, weak secrets)
- [ ] Form submission forwarding
- [ ] Error logging functionality
- [ ] MetForm dependency check

## Level 3 Implementation Plan

### Proposed Enhancements

#### 1. Retry Logic
```php
class WebhookQueue {
    const TABLE_NAME = 'metform_n8n_queue';
    
    public function queue_webhook($payload, $webhook_url, $secret) {
        // Add to queue with retry count
    }
    
    public function process_queue() {
        // Background processing via WP Cron
    }
}
```

#### 2. Advanced Logging
```php
class WebhookLogger {
    const TABLE_NAME = 'metform_n8n_logs';
    
    public function log_webhook($status, $payload, $response, $execution_time) {
        // Store detailed logs in database
    }
    
    public function get_logs($filters = []) {
        // Retrieve logs with filtering/pagination
    }
}
```

#### 3. Statistics Dashboard
```php
class WebhookStats {
    public function get_success_rate($period = '30days') {
        // Calculate success/failure rates
    }
    
    public function get_performance_metrics() {
        // Response times, throughput, etc.
    }
}
```

## Migration Path

### From Version 1.0 to 2.0
1. Extract hardcoded configuration
2. Prompt user to configure via admin panel
3. Maintain backward compatibility during transition

### Future Versions
- Database migrations for new tables (queue, logs)
- Settings migration for new options
- Data export/import functionality

## Development Environment

### Requirements
- PHP 7.4+ (WordPress minimum)
- WordPress 5.0+
- MetForm plugin
- n8n instance for testing

### Setup
1. Clone/download plugin to WordPress installation
2. Install MetForm plugin
3. Set up local n8n instance with webhook
4. Configure test webhook endpoint
5. Enable WordPress debug logging

### Debugging

```php
// Enable verbose logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Test webhook manually
$webhook = new MetFormN8nWebhook();
$webhook->handle_form_submission(123, ['test' => 'data']);
```