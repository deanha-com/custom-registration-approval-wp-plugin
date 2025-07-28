<?php
if (!defined('ABSPATH')) exit;

function cra_render_registration_form()
{
    ob_start();

    $errors = [];
    $success = false;
    $approval_status = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cra_submit'])) {
        $result = cra_handle_registration_form();

        if (isset($result['errors']) && !empty($result['errors'])) {
            $errors = $result['errors'];
        } elseif (isset($result['success']) && $result['success'] === true) {
            $success = true;
            $approval_status = $result['approval_status'] ?? '';
        }
    }

    if ($success) {
        if ($approval_status === 'approved') {
?>
            <div class="cra-success-message" style="padding:20px; border: 1px solid #4caf50; background: #dff0d8; color: #3c763d;">
                <h2>Registration successful!</h2>
                <p>Your account has been auto-approved. You may now <a href="<?php echo get_home_url() . '/login'; ?>">log in here</a>.</p>
            </div>
        <?php
        } else {
        ?>
            <div class="cra-success-message" style="padding:20px; border: 1px solid #31708f; background: #d9edf7; color: #31708f;">
                <h2>Registration received</h2>
                <p>Your registration is pending approval. We will notify you by email once your account is activated.</p>
            </div>
        <?php
        }
    } else {
        if (!empty($errors)) {
            echo '<ul class="cra-errors" style="padding-left: 20px;">';
            foreach ($errors as $err) {
                echo '<li>' . esc_html($err) . '</li>';
            }
            echo '</ul>';
        }
        ?>

        <form method="post" id="cra-register-form" enctype="multipart/form-data">
            <input type="text" name="cra_first_name" required placeholder="First Name"
                value="<?php echo isset($_POST['cra_first_name']) ? esc_attr($_POST['cra_first_name']) : ''; ?>">
            <input type="text" name="cra_last_name" required placeholder="Last Name"
                value="<?php echo isset($_POST['cra_last_name']) ? esc_attr($_POST['cra_last_name']) : ''; ?>">
            <input type="text"
                name="cra_username"
                required
                placeholder="Desired Username"
                pattern="^[A-Za-z0-9._]+$"
                title="Username may contain only letters, numbers, dot (.), and underscore (_)"
                value="<?php echo isset($_POST['cra_username']) ? esc_attr($_POST['cra_username']) : ''; ?>">
            <input type="text" name="cra_company" required placeholder="Company Name"
                value="<?php echo isset($_POST['cra_company']) ? esc_attr($_POST['cra_company']) : ''; ?>">
            <input type="text" name="cra_company_number" required placeholder="Company Number"
                value="<?php echo isset($_POST['cra_company_number']) ? esc_attr($_POST['cra_company_number']) : ''; ?>">
            <input type="text" name="cra_vat" placeholder="VAT Number (Optional)"
                value="<?php echo isset($_POST['cra_vat']) ? esc_attr($_POST['cra_vat']) : ''; ?>">
            <input type="text" name="cra_position_in_company" required placeholder="Position in the Company"
                value="<?php echo isset($_POST['cra_position_in_company']) ? esc_attr($_POST['cra_position_in_company']) : ''; ?>">
            <input type="text" name="cra_phone" required
                placeholder="Phone Number (enter N/A if unknown)"
                value="<?php echo isset($_POST['cra_phone']) ? esc_attr($_POST['cra_phone']) : ''; ?>">
            <input type="text" name="cra_company_address" required
                placeholder="Company Address (enter N/A if unknown)"
                value="<?php echo isset($_POST['cra_company_address']) ? esc_attr($_POST['cra_company_address']) : ''; ?>">
            <input type="text" name="cra_delivery_address" required
                id="cra_delivery_address"
                placeholder="Goods Delivery Address (enter N/A if same as company address)"
                value="<?php echo isset($_POST['cra_delivery_address']) ? esc_attr($_POST['cra_delivery_address']) : ''; ?>">

            <label style="display:block;margin-bottom:12px;">
                <input type="checkbox" id="cra_same_address" />
                Same as company address
            </label>

            <input type="text" name="cra_website"
                placeholder="Website (optional)"
                value="<?php echo isset($_POST['cra_website']) ? esc_attr($_POST['cra_website']) : ''; ?>">
            <textarea name="cra_comments" rows="4" placeholder="Other Comments / Notes (optional)"><?php echo isset($_POST['cra_comments']) ? esc_textarea($_POST['cra_comments']) : ''; ?></textarea>

            <input type="email" name="cra_email" required placeholder="Email"
                value="<?php echo isset($_POST['cra_email']) ? esc_attr($_POST['cra_email']) : ''; ?>">
            <input type="password" name="cra_password" required placeholder="Password">
            <input type="password" name="cra_password_confirm" required placeholder="Confirm Password">
            <?php wp_nonce_field('cra_register_form', 'cra_nonce'); ?>
            <button class="cra_submit_btn" type="submit" name="cra_submit" value="1">Register</button>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var usernameInput = document.querySelector('#cra-register-form input[name="cra_username"]');
                if (usernameInput) {
                    usernameInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^A-Za-z0-9._]/g, '');
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                var same = document.getElementById('cra_same_address');
                if (same) {
                    same.addEventListener('change', function() {
                        var comp = document.querySelector('input[name="cra_company_address"]');
                        var del = document.getElementById('cra_delivery_address');
                        if (this.checked) {
                            if (comp && del) del.value = comp.value;
                        }
                    });
                }
            });
        </script>



<?php
    }

    return ob_get_clean();
}

add_shortcode('custom_registration_form', 'cra_render_registration_form');
