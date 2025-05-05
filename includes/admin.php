<?php
if (!defined('ABSPATH')) {
    exit;
}

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

    add_submenu_page(
        'content-locker',
        'Email Marketing Settings',
        'Email Marketing',
        'manage_options',
        'content-locker-settings',
        'clec_settings_page'
    );

    add_submenu_page(
        'content-locker',
        'Form Design',
        'Form Design',
        'manage_options',
        'content-locker-form-design',
        'clec_form_design_page'
    );
}
add_action('admin_menu', 'clec_admin_menu');

// Register settings
function clec_register_settings() {
    register_setting('clec_settings', 'clec_mailchimp_api_key');
    register_setting('clec_settings', 'clec_mailchimp_list_id');
    register_setting('clec_settings', 'clec_enable_mailchimp');
    
    register_setting('clec_form_settings', 'clec_enable_first_name');
    register_setting('clec_form_settings', 'clec_enable_last_name');
    register_setting('clec_form_settings', 'clec_enable_phone');
    register_setting('clec_form_settings', 'clec_form_style');
    register_setting('clec_form_settings', 'clec_form_title');
    register_setting('clec_form_settings', 'clec_form_description');
    register_setting('clec_form_settings', 'clec_button_text');
    register_setting('clec_form_settings', 'clec_button_color');
    register_setting('clec_form_settings', 'clec_form_bg_color');
}
add_action('admin_init', 'clec_register_settings');

// Admin pages content
function clec_admin_page() {
    // ... existing code ...
}

function clec_settings_page() {
    // ... existing code ...
}

function clec_form_design_page() {
    // ... existing code ...
}