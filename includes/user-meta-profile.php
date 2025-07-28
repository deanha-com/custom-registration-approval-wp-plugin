<?php
if (!defined('ABSPATH')) exit;
ob_start();
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
            <th><label for="position_in_company">Position in Company</label></th>
            <td>
                <input type="text" name="position_in_company" id="position_in_company" value="<?php echo esc_attr(get_user_meta($user->ID, 'position_in_company', true)); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="phone">Phone</label></th>
            <td>
                <input type="text" name="phone" id="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>" class="regular-text" />
            </td>
        </tr>

        <tr>
            <th><label for="company_address">Company Address</label></th>
            <td>
                <input type="text" name="company_address" id="company_address" value="<?php echo esc_attr(get_user_meta($user->ID, 'company_address', true)); ?>" class="regular-text" />
            </td>
        </tr>

        <tr>
            <th><label for="delivery_address">Delivery Address</label></th>
            <td>
                <input type="text" name="delivery_address" id="delivery_address" value="<?php echo esc_attr(get_user_meta($user->ID, 'delivery_address', true)); ?>" class="regular-text" />
            </td>
        </tr>

        <tr>
            <th><label for="website">Website</label></th>
            <td>
                <input type="text" name="website" id="website" value="<?php echo esc_attr(get_user_meta($user->ID, 'website', true)); ?>" class="regular-text" />
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
    update_user_meta($user_id, 'position_in_company', sanitize_text_field($_POST['position_in_company']));
}
?>

<?php
// Show meta data on My Account (WooCommerce-compatible)
// 1. Add the Company Details tab to Woo My Account nav
add_filter('woocommerce_account_menu_items', function ($items) {
    // Insert "Company Details" after "Account Details"
    $new = [];
    foreach ($items as $key => $label) {
        $new[$key] = $label;
        if ($key === 'edit-account') {
            $new['company-details'] = __('Company Details', 'cra-plugin');
        }
    }
    return $new;
}, 20);

// 2. Register the custom endpoint
add_action('init', function () {
    add_rewrite_endpoint('company-details', EP_PAGES);
});

// 3. Display the company details in the tab
add_action('woocommerce_account_company-details_endpoint', function () {
    $user_id = get_current_user_id();

    // Handle success/error messages display
    if (!empty($_GET['updated']) && $_GET['updated'] === '1') {
        echo '<div class="woocommerce-message" role="alert">Company details updated.</div>';
    }

    // Get values
    $company_name = esc_attr(get_user_meta($user_id, 'company_name', true));
    $company_number = esc_attr(get_user_meta($user_id, 'company_number', true));
    $vat_number = esc_attr(get_user_meta($user_id, 'vat_number', true));
    $position_in_company = esc_attr(get_user_meta($user_id, 'position_in_company', true));
    $phone = esc_attr(get_user_meta($user_id, 'phone', true));
    $company_address = esc_attr(get_user_meta($user_id, 'company_address', true));
    $delivery_address = esc_attr(get_user_meta($user_id, 'delivery_address', true));
    $website = esc_attr(get_user_meta($user_id, 'website', true));
    $comments = esc_textarea(get_user_meta($user_id, 'comments', true));

?>
    <form method="post">
        <?php wp_nonce_field('cra_update_company_details', 'cra_company_details_nonce'); ?>

        <div id="cra-company-info">
            <h3>Company Information</h3>
            <div class="cra-form-container" style="column-count: 2;">

                <p>
                    <label>Company Name<br>
                        <input style="width: 100%;" type="text" name="company_name" value="<?php echo $company_name; ?>"
                            <?php echo apply_filters('cra_company_name_editable', 'disabled'); ?>
                            title="To update your company name, please email us from your account email to request a change." />
                    </label>
                </p>
                <p>
                    <label>Company Number<br>
                        <input style="width: 100%;" type="text" name="company_number" value="<?php echo $company_number; ?>"
                            <?php echo apply_filters('cra_company_number_editable', 'disabled'); ?>
                            title="To update your company number, please email us from your account email to request a change." />
                    </label>
                </p>
                <p>
                    <label>Company Address<br>
                        <input style="width: 100%;" type="text" name="cra_company_address" value="<?php echo $company_address; ?>" />
                    </label>
                </p>
                <p>
                    <label>Delivery Address<br>
                        <input style="width: 100%;" type="text" name="cra_delivery_address" value="<?php echo $delivery_address; ?>" />
                    </label>
                </p>
                <p>
                    <label>VAT Number<br>
                        <input style="width: 100%;" type="text" name="vat_number" value="<?php echo $vat_number; ?>" />
                    </label>
                </p>
                <p>
                    <label>Position in Company<br>
                        <input style="width: 100%;" type="text" name="position_in_company" value="<?php echo $position_in_company; ?>" />
                    </label>
                </p>
                <p>
                    <label>Phone Number<br>
                        <input style="width: 100%;" type="text" name="cra_phone" value="<?php echo $phone; ?>" />
                    </label>
                </p>

                <p>
                    <label>Website<br>
                        <input style="width: 100%;" type="text" name="cra_website" value="<?php echo $website; ?>" />
                    </label>
                </p>
                <!-- <p>
                    <label>Comments/Notes<br>
                        <textarea style="width: 100%;" name="cra_comments"><?php //echo $comments; 
                                                                            ?></textarea>
                    </label>
                </p> -->
            </div>
            <p>
                <button type="submit" class="button" name="cra_company_details_submit" value="1">Save Changes</button>
            </p>
        </div>
    </form>
<?php
});


add_action('template_redirect', function () {
    if (
        is_account_page() &&
        isset($_POST['cra_company_details_submit']) &&
        isset($_POST['cra_company_details_nonce']) &&
        wp_verify_nonce($_POST['cra_company_details_nonce'], 'cra_update_company_details')
    ) {
        $user_id = get_current_user_id();

        // Only update editable fields!
        if (isset($_POST['vat_number'])) update_user_meta($user_id, 'vat_number', sanitize_text_field($_POST['vat_number']));
        if (isset($_POST['position_in_company'])) update_user_meta($user_id, 'position_in_company', sanitize_text_field($_POST['position_in_company']));
        if (isset($_POST['cra_phone'])) update_user_meta($user_id, 'phone', sanitize_text_field($_POST['cra_phone']));
        if (isset($_POST['cra_company_address'])) update_user_meta($user_id, 'company_address', sanitize_text_field($_POST['cra_company_address']));
        if (isset($_POST['cra_delivery_address'])) update_user_meta($user_id, 'delivery_address', sanitize_text_field($_POST['cra_delivery_address']));
        if (isset($_POST['cra_website'])) update_user_meta($user_id, 'website', sanitize_text_field($_POST['cra_website']));
        if (isset($_POST['cra_comments'])) update_user_meta($user_id, 'comments', sanitize_textarea_field($_POST['cra_comments']));

        // Redirect to avoid resubmission and show success
        wp_redirect(add_query_arg('updated', 1, wc_get_account_endpoint_url('company-details')));
        exit;
    }
});

ob_end_clean();
