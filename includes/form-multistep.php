<?php
if (!defined('ABSPATH')) exit;

// Register shortcode
add_shortcode('cra_multistep_registration_form', 'cra_render_multistep_registration_form');
add_action('wp_enqueue_scripts', 'cra_enqueue_multistep_assets');

function cra_render_multistep_registration_form()
{
    ob_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $errors = cra_handle_registration_form();

        // cra_handle_registration_form will redirect & exit on success
        // If it didn't redirect, then either:
        // - errors occurred
        // - or we want to control success messaging manually

        if (!empty($errors) && is_array($errors)) {
            echo '<ul class="cra-errors">';
            foreach ($errors as $err) {
                echo '<li>' . esc_html($err) . '</li>';
            }
            echo '</ul>';
        } else {
            // Get user by submitted email
            $user = get_user_by('email', sanitize_email($_POST['cra_email']));
            if ($user) {
                $status = get_user_meta($user->ID, 'cra_approval_status', true);
                $login_url = wp_login_url();

                echo '<div class="cra-success-box">';
                if ($status === 'approved') {
                    echo '<h2>Your account was approved!</h2>';
                    echo '<p>You can now <a href="' . esc_url($login_url) . '">log in here</a>.</p>';
                } else {
                    echo '<h2>Registration Successful 🎉</h2>';
                    echo '<p>Your application will be reviewed. We’ll notify you by email after approval.</p>';
                }
                echo '</div>';

                // Stop showing the form below
                return ob_get_clean();
            }
        }
    }

    // Load assets
    wp_enqueue_script('cra-multistep-js', plugins_url('../assets/multistep.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_style('cra-multistep-css', plugins_url('../assets/multistep.css', __FILE__));

?>
    <!-- Start Form HTML -->
    <form id="cra-multistep-form" method="post">
        <?php wp_nonce_field('cra_multistep_register', 'cra_nonce'); ?>

        <!-- Progress bar -->
        <ul id="cra-progressbar">
            <li class="active">Account</li>
            <li>Company</li>
            <li>Review</li>
        </ul>

        <!-- Step 1 -->
        <fieldset>
            <h2>Step 1: Account Information</h2>
            <input type="text" name="cra_first_name" placeholder="First Name" required />
            <input type="text" name="cra_last_name" placeholder="Last Name" required />
            <input type="email" name="cra_email" placeholder="Email" required />
            <input type="text" name="cra_username" placeholder="Username" required />
            <input type="password" name="cra_password" placeholder="Password" required />
            <input type="password" name="cra_password_confirm" placeholder="Confirm Password" required />
            <input type="button" name="next" class="next action-button" value="Next" />
        </fieldset>

        <!-- Step 2 -->
        <fieldset>
            <h2>Step 2: Company Information</h2>
            <input type="text" name="cra_company" placeholder="Company Name" required />
            <input type="text" name="cra_company_number" id="cra_company_number" placeholder="Company Number" required />
            <small id="company-valid-message" style="display:none;color:green;"></small>
            <input type="text" name="cra_vat" placeholder="VAT Number (Optional)" />
            <input type="text" name="cra_company_role" placeholder="Your Role in Company" required />
            <input type="button" name="previous" class="previous action-button" value="Back" />
            <input type="button" name="next" id="cra-company-next" class="next action-button" value="Next" />
        </fieldset>

        <!-- Step 3 -->
        <fieldset>
            <h2>Step 3: Review & Submit</h2>
            <div id="cra-review-summary"></div>
            <input type="button" name="previous" class="previous action-button" value="Back" />
            <input type="submit" name="submit" class="submit action-button" value="Register" />
        </fieldset>

        <!-- (Success message will be shown above if user approved or pending) -->
    </form>
<?php

    return ob_get_clean();
}
