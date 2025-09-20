<?php
/**
 * Plugin Name: MetForm → n8n Webhook Bridge
 * Description: Sends MetForm data to n8n via webhook immediately and securely.
 * Version: 2.0
 * Plugin URI: https://github.com/ibernabel/metform-n8n-webhook-bridge
 * Author: Idequel Bernabel
 * Author URI: https://github.com/ibernabel
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: metform-n8n-webhook-bridge
 */
if (!defined('ABSPATH')) {
    exit; // Security check
}

class MetFormN8nWebhook {
    
    private const OPTION_WEBHOOK_URL = 'metform_n8n_webhook_url';
    private const OPTION_SECRET = 'metform_n8n_secret';
    private const OPTION_ENABLED = 'metform_n8n_enabled';
    
    public function __construct() {
        add_action('init', [$this, 'init']);
    }
    
    public function init() {
        // Only proceed if MetForm is active
        if (!$this->is_metform_active()) {
            add_action('admin_notices', [$this, 'show_metform_required_notice']);
            return;
        }
        
        // Hook into MetForm submissions
        add_action('metform_after_store_form_data', [$this, 'handle_form_submission'], 10, 2);
        
        // Add admin menu and settings
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Check if MetForm plugin is active
     */
    private function is_metform_active(): bool {
        return class_exists('MetForm\\Core\\Entries\\Action');
    }
    
    /**
     * Show notice if MetForm is not active
     */
    public function show_metform_required_notice() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>MetForm → n8n Webhook:</strong> ';
        echo esc_html__('MetForm plugin is required for this plugin to work.', 'metform-n8n-webhook');
        echo '</p></div>';
    }
    
    /**
     * Handle form submission and send to webhook
     */
    public function handle_form_submission($form_id, $response) {
        // Check if integration is enabled
        if (!$this->is_integration_enabled()) {
            return;
        }
        
        $webhook_url = $this->get_webhook_url();
        $secret = $this->get_secret();
        
        // Validate configuration
        if (!$this->validate_configuration($webhook_url, $secret)) {
            $this->log_error('Invalid configuration: missing webhook URL or secret');
            return;
        }
        
        // Prepare payload
        $payload = $this->prepare_payload($form_id, $response);
        
        // Send to webhook
        $this->send_webhook($webhook_url, $secret, $payload);
    }
    
    /**
     * Validate webhook configuration
     */
    private function validate_configuration($webhook_url, $secret): bool {
        if (empty($webhook_url) || empty($secret)) {
            return false;
        }
        
        // Validate URL format
        if (!filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Ensure HTTPS for security
        if (strpos($webhook_url, 'https://') !== 0) {
            $this->log_error('Webhook URL must use HTTPS for security');
            return false;
        }
        
        // Validate secret strength (minimum 12 characters)
        if (strlen($secret) < 12) {
            $this->log_error('Secret must be at least 12 characters long');
            return false;
        }
        
        return true;
    }
    
    /**
     * Prepare payload for webhook
     */
    private function prepare_payload($form_id, $response): array {
        return [
            'form_id' => sanitize_text_field($form_id),
            'fields' => $this->sanitize_form_data($response),
            'timestamp' => current_time('c'), // ISO 8601 format
            'site_url' => get_site_url(),
        ];
    }
    
    /**
     * Sanitize form data recursively
     */
    private function sanitize_form_data($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize_form_data'], $data);
        }
        
        return sanitize_textarea_field($data);
    }
    
    /**
     * Send data to webhook
     */
    private function send_webhook($webhook_url, $secret, $payload) {
        $request = wp_remote_post($webhook_url, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Webhook-Secret' => $secret,
                'User-Agent' => 'MetForm-n8n-Webhook/2.0 WordPress/' . get_bloginfo('version'),
            ],
            'body' => wp_json_encode($payload),
            'timeout' => 30, // Increased timeout
            'sslverify' => true, // Ensure SSL verification
        ]);
        
        $this->handle_webhook_response($request, $payload);
    }
    
    /**
     * Handle webhook response and logging
     */
    private function handle_webhook_response($request, $payload) {
        if (is_wp_error($request)) {
            $this->log_error('Webhook request failed: ' . $request->get_error_message(), $payload);
            return;
        }
        
        $response_code = wp_remote_retrieve_response_code($request);
        $response_body = wp_remote_retrieve_body($request);
        
        if ($response_code === 200) {
            $this->log_success('Webhook sent successfully', $payload);
        } else {
            $this->log_error(
                "Unexpected response from webhook: {$response_code}",
                [
                    'payload' => $payload,
                    'response_body' => $response_body
                ]
            );
        }
    }
    
    /**
     * Log error messages
     */
    private function log_error($message, $context = []) {
        $log_message = '[MetForm→n8n] ERROR: ' . $message;
        
        if (!empty($context)) {
            $log_message .= ' | Context: ' . wp_json_encode($context);
        }
        
        error_log($log_message);
    }
    
    /**
     * Log success messages (only in debug mode)
     */
    private function log_success($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = '[MetForm→n8n] SUCCESS: ' . $message;
            
            if (!empty($context)) {
                $log_message .= ' | Context: ' . wp_json_encode($context);
            }
            
            error_log($log_message);
        }
    }
    
    /**
     * Get webhook URL from options
     */
    private function get_webhook_url(): string {
        return get_option(self::OPTION_WEBHOOK_URL, '');
    }
    
    /**
     * Get secret from options
     */
    private function get_secret(): string {
        return get_option(self::OPTION_SECRET, '');
    }
    
    /**
     * Check if integration is enabled
     */
    private function is_integration_enabled(): bool {
        return get_option(self::OPTION_ENABLED, false);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'MetForm n8n Webhook',
            'MetForm → n8n',
            'manage_options',
            'metform-n8n-webhook',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('metform_n8n_webhook', self::OPTION_WEBHOOK_URL, [
            'sanitize_callback' => [$this, 'sanitize_webhook_url']
        ]);
        
        register_setting('metform_n8n_webhook', self::OPTION_SECRET, [
            'sanitize_callback' => [$this, 'sanitize_secret']
        ]);
        
        register_setting('metform_n8n_webhook', self::OPTION_ENABLED, [
            'sanitize_callback' => 'rest_sanitize_boolean'
        ]);
    }
    
    /**
     * Sanitize webhook URL
     */
    public function sanitize_webhook_url($url) {
        $url = esc_url_raw($url);
        
        if (!empty($url) && strpos($url, 'https://') !== 0) {
            add_settings_error('metform_n8n_webhook', 'invalid_url', 
                'Webhook URL must use HTTPS for security.');
            return get_option(self::OPTION_WEBHOOK_URL, '');
        }
        
        return $url;
    }
    
    /**
     * Sanitize secret
     */
    public function sanitize_secret($secret) {
        $secret = sanitize_text_field($secret);
        
        if (!empty($secret) && strlen($secret) < 12) {
            add_settings_error('metform_n8n_webhook', 'weak_secret', 
                'Secret must be at least 12 characters long for security.');
            return get_option(self::OPTION_SECRET, '');
        }
        
        return $secret;
    }
    
    /**
     * Admin page HTML
     */
    public function admin_page() {
        if (isset($_POST['submit'])) {
            // Handle form submission
            update_option(self::OPTION_WEBHOOK_URL, $_POST[self::OPTION_WEBHOOK_URL]);
            update_option(self::OPTION_SECRET, $_POST[self::OPTION_SECRET]);
            update_option(self::OPTION_ENABLED, isset($_POST[self::OPTION_ENABLED]));
            
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        $webhook_url = $this->get_webhook_url();
        $secret = $this->get_secret();
        $enabled = $this->is_integration_enabled();
        
        ?>
        <div class="wrap">
            <h1>MetForm → n8n Webhook Settings</h1>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Integration</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo self::OPTION_ENABLED; ?>" value="1" <?php checked($enabled); ?>>
                                Enable webhook forwarding
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td>
                            <input type="url" name="<?php echo self::OPTION_WEBHOOK_URL; ?>" 
                                   value="<?php echo esc_attr($webhook_url); ?>" 
                                   class="regular-text" placeholder="https://your-n8n-domain.com/webhook/endpoint" required>
                            <p class="description">Must use HTTPS for security.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Webhook Secret</th>
                        <td>
                            <input type="password" name="<?php echo self::OPTION_SECRET; ?>" 
                                   value="<?php echo esc_attr($secret); ?>" 
                                   class="regular-text" placeholder="Enter a strong secret (min 12 chars)" required>
                            <p class="description">Used for authentication. Minimum 12 characters.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <div class="card">
                <h2>n8n Configuration</h2>
                <p>In your n8n webhook node, add this JavaScript code to validate the secret:</p>
                <code style="display: block; background: #f1f1f1; padding: 10px; margin: 10px 0;">
// Check webhook secret<br>
const receivedSecret = $request.headers['x-webhook-secret'];<br>
const expectedSecret = 'YOUR_SECRET_HERE';<br>
<br>
if (receivedSecret !== expectedSecret) {<br>
&nbsp;&nbsp;$respond.status(401).json({error: 'Invalid secret'});<br>
&nbsp;&nbsp;return;<br>
}<br>
<br>
// Process the data<br>
return $input.all();
                </code>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
new MetFormN8nWebhook();