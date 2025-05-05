<?php
if (!defined('ABSPATH')) {
    exit;
}

function clec_mailchimp_subscribe($email) {
    if (!get_option('clec_enable_mailchimp') || !get_option('clec_mailchimp_api_key') || !get_option('clec_mailchimp_list_id')) {
        return;
    }

    $api_key = get_option('clec_mailchimp_api_key');
    $list_id = get_option('clec_mailchimp_list_id');
    
    $dc = substr($api_key, strpos($api_key, '-') + 1);
    $url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members";
    
    $data = json_encode([
        'email_address' => $email,
        'status' => 'subscribed'
    ]);
    
    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
            'Content-Type' => 'application/json'
        ],
        'body' => $data
    ]);
    
    if (is_wp_error($response)) {
        error_log('Mailchimp API Error: ' . $response->get_error_message());
    }
}
add_action('clec_after_email_submission', 'clec_mailchimp_subscribe');