<?php
/*
 * Plugin Name:       Content Locker for Email Capture
 * Plugin URI:        https://wpshopifyexpert.com/plugins/content-locker-for-email-capture
 * Description:       A plugin for content locking and email capture.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            WP Shopify Expert
 * Author URI:        https://wpshopifyexpert.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-pdf-embedder
 * Domain Path:       /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Create database table on plugin activation
function clec_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'content_locker_emails';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'clec_create_table');

// Add shortcode for content locker
function clec_content_locker($atts, $content = null) {
    // Check if user's email is in cookie
    if (isset($_COOKIE['clec_email_verified'])) {
        return do_shortcode($content);
    }
    
    $output = '<div class="content-locker-wrapper">';
    $output .= '<div class="locked-content" style="display:none;">' . do_shortcode($content) . '</div>';
    $output .= '<div class="email-capture-form">';
    $output .= '<h3>This content is locked</h3>';
    $output .= '<p>Please enter your email to access this content:</p>';
    $output .= '<form id="clec-email-form">';
    $output .= '<input type="email" id="clec-email" required placeholder="Enter your email">';
    $output .= '<button type="submit">Unlock Content</button>';
    $output .= '</form>';
    $output .= '<div id="clec-message"></div>';
    $output .= '</div></div>';
    
    return $output;
}
add_shortcode('content_lock', 'clec_content_locker');

// Add necessary scripts and styles
function clec_enqueue_scripts() {
    wp_enqueue_style('clec-styles', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('clec-script', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script('clec-script', 'clec_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('clec_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'clec_enqueue_scripts');

// Handle AJAX email submission
function clec_handle_email_submission() {
    check_ajax_referer('clec_nonce', 'nonce');
    
    $email = sanitize_email($_POST['email']);
    
    if (!is_email($email)) {
        wp_send_json_error('Invalid email address');
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'content_locker_emails';
    
    $wpdb->insert(
        $table_name,
        array('email' => $email),
        array('%s')
    );

    // Mailchimp Integration
    if (get_option('clec_enable_mailchimp') && get_option('clec_mailchimp_api_key') && get_option('clec_mailchimp_list_id')) {
        $api_key = get_option('clec_mailchimp_api_key');
        $list_id = get_option('clec_mailchimp_list_id');
        
        // Get datacenter from API key
        $dc = substr($api_key, strpos($api_key, '-') + 1);
        
        // API endpoint
        $url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members";
        
        // Prepare data
        $data = json_encode([
            'email_address' => $email,
            'status' => 'subscribed'
        ]);
        
        // Make API request
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                'Content-Type' => 'application/json'
            ],
            'body' => $data
        ]);
        
        // Log Mailchimp errors if any
        if (is_wp_error($response)) {
            error_log('Mailchimp API Error: ' . $response->get_error_message());
        }
    }
    
    // Set cookie for 30 days
    setcookie('clec_email_verified', '1', time() + (30 * DAY_IN_SECONDS), '/');
    
    wp_send_json_success('Email verified successfully');
}
add_action('wp_ajax_clec_submit_email', 'clec_handle_email_submission');
add_action('wp_ajax_nopriv_clec_submit_email', 'clec_handle_email_submission');

// Add admin menu
function clec_admin_menu() {
    add_menu_page(
        'Content Locker',
        'Content Locker',
        'manage_options',
        'content-locker',
        'clec_admin_page',
        'dashicons-lock'
    );

    // Add submenu for email marketing settings
    add_submenu_page(
        'content-locker',
        'Email Marketing Settings',
        'Email Marketing',
        'manage_options',
        'content-locker-settings',
        'clec_settings_page'
    );
}
add_action('admin_menu', 'clec_admin_menu');

// Register settings
function clec_register_settings() {
    register_setting('clec_settings', 'clec_mailchimp_api_key');
    register_setting('clec_settings', 'clec_mailchimp_list_id');
    register_setting('clec_settings', 'clec_enable_mailchimp');
}
add_action('admin_init', 'clec_register_settings');

// Settings page content
function clec_settings_page() {
    ?>
    <div class="wrap">
        <h1>Email Marketing Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('clec_settings');
            do_settings_sections('clec_settings');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Enable Mailchimp Integration</th>
                    <td>
                        <label>
                            <input type="checkbox" name="clec_enable_mailchimp" value="1" <?php checked(get_option('clec_enable_mailchimp'), 1); ?>>
                            Enable Mailchimp
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mailchimp API Key</th>
                    <td>
                        <input type="text" name="clec_mailchimp_api_key" value="<?php echo esc_attr(get_option('clec_mailchimp_api_key')); ?>" class="regular-text">
                        <p class="description">Enter your Mailchimp API key. You can find it in your Mailchimp account under Account → Extras → API keys.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mailchimp List ID</th>
                    <td>
                        <input type="text" name="clec_mailchimp_list_id" value="<?php echo esc_attr(get_option('clec_mailchimp_list_id')); ?>" class="regular-text">
                        <p class="description">Enter your Mailchimp List/Audience ID. You can find it in Mailchimp under Audience → Settings → Audience name and defaults.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Admin page content
function clec_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'content_locker_emails';
    $emails = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    
    ?>
    <div class="wrap">
        <h1>Content Locker Email List</h1>
        <div class="clec-admin-content">
            <h2>How to Use</h2>
            <p>Use the shortcode [content_lock]Your content here[/content_lock] to lock any content.</p>
            
            <h2>Collected Emails</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emails as $email): ?>
                        <tr>
                            <td><?php echo esc_html($email->email); ?></td>
                            <td><?php echo esc_html($email->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php 
}
?>