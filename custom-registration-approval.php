<?php
/*
Plugin Name: Custom Registration Approval
Description: Provides a secure custom registration form with company number validation.
Version: 1.0
Author: Dean H.
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
    $columns['company_role']    = 'Role in Company';
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
        case 'company_role':
            return esc_html(get_user_meta($user_id, 'role_in_company', true));
        default:
            return $value;
    }
}, 10, 3);


// Add custom fields to user profile
add_action('show_user_profile', 'cra_custom_user_profile_fields');
add_action('edit_user_profile', 'cra_custom_user_profile_fields');
function cra_custom_user_profile_fields($user)
{
?>
    <h2>Wholesale Company Information</h2>
    <table class="form-table">
        <tr>
            <th><label for="company_name">Company Name</label></th>
            <td>
                <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr(get_user_meta($user->ID, 'company_name', true)); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="company_number">Company Number</label></th>
            <td>
                <input type="text" name="company_number" id="company_number" value="<?php echo esc_attr(get_user_meta($user->ID, 'company_number', true)); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="vat_number">VAT Number</label></th>
            <td>
                <input type="text" name="vat_number" id="vat_number" value="<?php echo esc_attr(get_user_meta($user->ID, 'vat_number', true)); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="company_role">Role in Company</label></th>
            <td>
                <input type="text" name="company_role" id="company_role" value="<?php echo esc_attr(get_user_meta($user->ID, 'role_in_company', true)); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
<?php
}

// Save custom fields
add_action('personal_options_update', 'cra_save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'cra_save_custom_user_profile_fields');
function cra_save_custom_user_profile_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id)) return false;
    update_user_meta($user_id, 'company_name', sanitize_text_field($_POST['company_name']));
    update_user_meta($user_id, 'company_number', sanitize_text_field($_POST['company_number']));
    update_user_meta($user_id, 'vat_number', sanitize_text_field($_POST['vat_number']));
    update_user_meta($user_id, 'role_in_company', sanitize_text_field($_POST['company_role']));
}


// Show meta data on My Account (WooCommerce-compatible)
add_action('woocommerce_edit_account_form', function () {
    $user_id = get_current_user_id();
?>
    <div id="cra-company-info">
        <h3>Company Information</h3>
        <p>
            <label>Company Name<br>
                <input style="width: 100%;" type="text" name="company_name" value="<?php echo esc_attr(get_user_meta($user_id, 'company_name', true)); ?>" disabled
                    title="To update your company name, please email us from your account email to request a change." />
            </label>
        </p>
        <p>
            <label>Company Number<br>
                <input style="width: 100%;" type="text" name="company_number" value="<?php echo esc_attr(get_user_meta($user_id, 'company_number', true)); ?>" disabled
                    title="To update your company number, please email us from your account email to request a change." />
            </label>
        </p>
        <p>
            <label>VAT Number<br>
                <input style="width: 100%;" type="text" name="vat_number" value="<?php echo esc_attr(get_user_meta($user_id, 'vat_number', true)); ?>" />
            </label>
        </p>
        <p>
            <label>Role in Company<br>
                <input style="width: 100%;" type="text" name="company_role" value="<?php echo esc_attr(get_user_meta($user_id, 'role_in_company', true)); ?>" />
            </label>
        </p>
    </div>
<?php
});

// Save data from My Account
add_action('woocommerce_save_account_details', function ($user_id) {
    if (isset($_POST['company_name'])) update_user_meta($user_id, 'company_name', sanitize_text_field($_POST['company_name']));
    if (isset($_POST['company_number'])) update_user_meta($user_id, 'company_number', sanitize_text_field($_POST['company_number']));
    if (isset($_POST['vat_number'])) update_user_meta($user_id, 'vat_number', sanitize_text_field($_POST['vat_number']));
    if (isset($_POST['company_role'])) update_user_meta($user_id, 'role_in_company', sanitize_text_field($_POST['company_role']));
});
