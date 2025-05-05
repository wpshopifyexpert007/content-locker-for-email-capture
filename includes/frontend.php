<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add shortcode for content locker
function clec_content_locker($atts, $content = null) {
    if (isset($_COOKIE['clec_email_verified'])) {
        return do_shortcode($content);
    }
    
    $form_title = get_option('clec_form_title', 'This content is locked');
    $form_description = get_option('clec_form_description', 'Please enter your email to access this content:');
    $button_text = get_option('clec_button_text', 'Unlock Content');
    $form_style = get_option('clec_form_style', 'default');
    $button_color = get_option('clec_button_color', '#0073aa');
    $form_bg_color = get_option('clec_form_bg_color', '#f9f9f9');
    
    ob_start();
    include CLEC_PLUGIN_DIR . 'templates/form.php';
    return ob_get_clean();
}
add_shortcode('content_lock', 'clec_content_locker');

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

    do_action('clec_after_email_submission', $email);
    
    setcookie('clec_email_verified', '1', time() + (30 * DAY_IN_SECONDS), '/');
    
    wp_send_json_success('Email verified successfully');
}
add_action('wp_ajax_clec_submit_email', 'clec_handle_email_submission');
add_action('wp_ajax_nopriv_clec_submit_email', 'clec_handle_email_submission');