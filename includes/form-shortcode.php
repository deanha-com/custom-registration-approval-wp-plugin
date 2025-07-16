<?php

if (!defined('ABSPATH')) exit;

function cra_render_registration_form()
{
    ob_start();

    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cra_submit'])) {
        $errors = cra_handle_registration_form();

        // redirection and exit will happen inside cra_handle_registration_form() if registration succeeds.
        // Only show errors below if present.
        if (!empty($errors) && is_array($errors)) {
            echo '<ul class="cra-errors">';
            foreach ($errors as $err) {
                echo '<li>' . esc_html($err) . '</li>';
            }
            echo '</ul>';
        }
    }
?>
    <!-- Form HTML here (only rendered if not redirected) -->
    <form method="post" id="cra-register-form" enctype="multipart/form-data">
        <input type="text" name="cra_first_name" required placeholder="First Name" value="<?php echo isset($_POST['cra_first_name']) ? esc_attr($_POST['cra_first_name']) : ''; ?>">
        <input type="text" name="cra_last_name" required placeholder="Last Name" value="<?php echo isset($_POST['cra_last_name']) ? esc_attr($_POST['cra_last_name']) : ''; ?>">
        <input type="text" name="cra_username" required placeholder="Desired Username" value="<?php echo isset($_POST['cra_username']) ? esc_attr($_POST['cra_username']) : ''; ?>">
        <input type="text" name="cra_company" required placeholder="Company Name" value="<?php echo isset($_POST['cra_company']) ? esc_attr($_POST['cra_company']) : ''; ?>">
        <input type="text" name="cra_company_number" required placeholder="Company Number" value="<?php echo isset($_POST['cra_company_number']) ? esc_attr($_POST['cra_company_number']) : ''; ?>">
        <input type="text" name="cra_vat" placeholder="VAT Number (Optional)" value="<?php echo isset($_POST['cra_vat']) ? esc_attr($_POST['cra_vat']) : ''; ?>">
        <input type="text" name="cra_company_role" required placeholder="Role in the Company" value="<?php echo isset($_POST['cra_company_role']) ? esc_attr($_POST['cra_company_role']) : ''; ?>">
        <input type="email" name="cra_email" required placeholder="Email" value="<?php echo isset($_POST['cra_email']) ? esc_attr($_POST['cra_email']) : ''; ?>">
        <input type="password" name="cra_password" required placeholder="Password">
        <input type="password" name="cra_password_confirm" required placeholder="Confirm Password">
        <?php wp_nonce_field('cra_register_form', 'cra_nonce'); ?>
        <button type="submit" name="cra_submit" value="1">Register</button>
    </form>
<?php
    return ob_get_clean();
}

add_shortcode('custom_registration_form', 'cra_render_registration_form');
