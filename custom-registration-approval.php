<?php
/*
Plugin Name: Custom Registration Approval
Description: Provides a secure custom registration form with UK company number & VAT validation.
Version: 1.1
Author: Dean Ha
Author URI: https://deanha.com
*/

if (!defined('ABSPATH')) exit;

// Include files
require_once plugin_dir_path(__FILE__) . 'includes/form-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/validation.php';
require_once plugin_dir_path(__FILE__) . 'includes/approval.php';
require_once plugin_dir_path(__FILE__) . 'includes/restrict-guest-access.php';
// New multistep form and AJAX
require_once plugin_dir_path(__FILE__) . 'includes/form-multistep.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-company-validator.php';
require_once plugin_dir_path(__FILE__) . 'includes/restricted-screen.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-meta-profile.php';


// (Optional) Load styles
function cra_enqueue_assets()
{
    wp_enqueue_style('cra-style', plugins_url('assets/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'cra_enqueue_assets');

function cra_enqueue_multistep_assets()
{


    $src = plugins_url('assets/multistep.js', __FILE__);
    wp_enqueue_script('cra-multistep-js', $src, ['jquery'], null, true);

    wp_localize_script('cra-multistep-js', 'cra_ajax_object', [
        'ajaxurl'  => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('cra_multistep_register')
    ]);

    wp_enqueue_style(
        'cra-multistep-css',
        plugins_url('assets/multistep.css', __FILE__)
    );
}

register_activation_hook(__FILE__, function () {
    add_role('pending', 'Pending Approval', ['read' => false]);
});

// Add custom columns to the Users table
add_filter('manage_users_columns', function ($columns) {
    $columns['company_name']    = 'Company Name';
    $columns['company_number']  = 'Company No.';
    $columns['vat_number']      = 'VAT No.';
    $columns['position_in_company']    = 'Position in Company';
    return $columns;
});

// Populate custom column data
add_action('manage_users_custom_column', function ($value, $column_name, $user_id) {
    switch ($column_name) {
        case 'company_name':
            return esc_html(get_user_meta($user_id, 'company_name', true));
        case 'company_number':
            return esc_html(get_user_meta($user_id, 'company_number', true));
        case 'vat_number':
            return esc_html(get_user_meta($user_id, 'vat_number', true));
        case 'position_in_company':
            return esc_html(get_user_meta($user_id, 'position_in_company', true));
        default:
            return $value;
    }
}, 10, 3);

// 4. Flush rewrite rules on plugin activation only (not on every load!)
register_activation_hook(__FILE__, function () {
    add_rewrite_endpoint('company-details', EP_PAGES);
    flush_rewrite_rules();
});
