# MetForm-n8n-Webhook-Bridge
This plugin sends MetForm submissions directly to an n8n webhook in real time. It uses `wp_remote_post()` to forward form data with a secure custom header, bypassing cron and Action Scheduler. Errors and unexpected responses are logged to the WordPress/PHP error log for reliable troubleshooting.
