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
                <p>Your account has been auto-approved. You may now <a href="<?php echo esc_url(get_home_url() . '/login'); ?>">log in here</a>.</p>
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

        <style>
            /* Responsive two-column form layout */
            #cra-register-form {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-gap: 1.5rem 2rem;
                /* max-width: 800px; */
                margin: 0 auto;
            }

            #cra-register-form p {
                margin: 0;
            }

            #cra-register-form label {
                display: block;
                font-weight: 600;
                margin-bottom: 0.3rem;
            }

            #cra-register-form input[type="text"],
            #cra-register-form input[type="email"],
            #cra-register-form input[type="password"],
            #cra-register-form textarea {
                width: 100%;
                padding: 0.5rem;
                font-size: 1rem;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
            }

            #cra-register-form textarea {
                resize: vertical;
                min-height: 80px;
            }

            /* Full width for textarea and checkbox label */
            #cra-register-form textarea,
            #cra-register-form .full-width {
                grid-column: 1 / -1;
            }

            /* Submit button full width */
            #cra-register-form .submit-wrapper {
                grid-column: 1 / -1;
                margin-top: 1.5rem;
                text-align: right;
            }

            #cra-register-form button.cra_submit_btn {
                background-color: #0073aa;
                color: white;
                font-size: 1rem;
                padding: 0.6rem 1.2rem;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            #cra-register-form button.cra_submit_btn:hover {
                background-color: #005177;
            }

            /* Checkbox label styling (span beside the checkbox) */
            #cra-register-form label.checkbox-label {
                display: flex;
                align-items: center;
                margin-bottom: 1rem;
                grid-column: 1 / -1;
                cursor: pointer;
                font-weight: normal;
                justify-content: flex-end;
            }

            #cra-register-form input[type="checkbox"] {
                margin-block: 5px;
                margin-right: 10px;
                width: auto;
            }

            /* Responsive: single column on small viewports */
            @media (max-width: 600px) {
                #cra-register-form {
                    grid-template-columns: 1fr !important;
                }

                #cra-register-form .submit-wrapper {
                    text-align: center;
                }
            }
        </style>

        <form method="post" id="cra-register-form" enctype="multipart/form-data" novalidate>
            <!-- First Name -->
            <p>
                <label for="cra_first_name">First Name <span style="color:red;">*</span></label>
                <input type="text" id="cra_first_name" name="cra_first_name" required placeholder="Enter your first name"
                    value="<?php echo isset($_POST['cra_first_name']) ? esc_attr($_POST['cra_first_name']) : ''; ?>">
            </p>

            <!-- Last Name -->
            <p>
                <label for="cra_last_name">Last Name <span style="color:red;">*</span></label>
                <input type="text" id="cra_last_name" name="cra_last_name" required placeholder="Enter your last name"
                    value="<?php echo isset($_POST['cra_last_name']) ? esc_attr($_POST['cra_last_name']) : ''; ?>">
            </p>

            <!-- Username -->
            <p>
                <label for="cra_username">Username <span style="color:red;">*</span></label>
                <input type="text" id="cra_username" name="cra_username" required
                    minlength="4"
                    maxlength="30"
                    placeholder="Allowed: letters, numbers, dot (.), underscore (_) only"
                    pattern="^[A-Za-z0-9._]+$"
                    title="Username may contain only letters, numbers, dot (.), and underscore (_)."
                    value="<?php echo isset($_POST['cra_username']) ? esc_attr($_POST['cra_username']) : ''; ?>">
            </p>

            <!-- Company Name -->
            <p>
                <label for="cra_company">Company Name <span style="color:red;">*</span></label>
                <input type="text" id="cra_company" name="cra_company" required placeholder="Enter your company name"
                    value="<?php echo isset($_POST['cra_company']) ? esc_attr($_POST['cra_company']) : ''; ?>">
            </p>

            <!-- Company Number -->
            <p>
                <label for="cra_company_number">Company Number <span style="color:red;">*</span></label>
                <input type="text" id="cra_company_number" name="cra_company_number" required placeholder="Enter your company number"
                    value="<?php echo isset($_POST['cra_company_number']) ? esc_attr($_POST['cra_company_number']) : ''; ?>">
            </p>

            <!-- VAT Number -->
            <p>
                <label for="cra_vat">VAT Number</label>
                <input type="text" id="cra_vat" name="cra_vat" placeholder="VAT number"
                    value="<?php echo isset($_POST['cra_vat']) ? esc_attr($_POST['cra_vat']) : ''; ?>">
            </p>

            <!-- Position in Company -->
            <p>
                <label for="cra_position_in_company">Position in the Company <span style="color:red;">*</span></label>
                <input type="text" id="cra_position_in_company" name="cra_position_in_company" required placeholder="Enter your position in company"
                    value="<?php echo isset($_POST['cra_position_in_company']) ? esc_attr($_POST['cra_position_in_company']) : ''; ?>">
            </p>

            <!-- Phone Number -->
            <p>
                <label for="cra_phone">Phone Number <span style="color:red;">*</span></label>
                <input type="text" id="cra_phone" name="cra_phone" required placeholder="Enter your phone number"
                    value="<?php echo isset($_POST['cra_phone']) ? esc_attr($_POST['cra_phone']) : ''; ?>">
            </p>

            <!-- Company Address -->
            <p>
                <label for="cra_company_address">Company Address <span style="color:red;">*</span></label>
                <input type="text" id="cra_company_address" name="cra_company_address" required placeholder="Enter your company address"
                    value="<?php echo isset($_POST['cra_company_address']) ? esc_attr($_POST['cra_company_address']) : ''; ?>">
            </p>

            <!-- Goods Delivery Address -->
            <p>
                <label for="cra_delivery_address">Goods Delivery Address <span style="color:red;">*</span></label>
                <input type="text" id="cra_delivery_address" name="cra_delivery_address" required placeholder="Enter goods delivery address"
                    value="<?php echo isset($_POST['cra_delivery_address']) ? esc_attr($_POST['cra_delivery_address']) : ''; ?>">
            </p>

            <!-- Checkbox: Same as company address -->
            <label for="cra_same_address" class="checkbox-label">
                <input type="checkbox" id="cra_same_address" />
                <span>Same as company address</span>
            </label>

            <!-- Comments / Notes -->
            <p class="full-width">
                <label for="cra_comments">Other Comments / Notes</label>
                <textarea id="cra_comments" name="cra_comments" rows="4" placeholder="Write anything that might be useful for us"><?php echo isset($_POST['cra_comments']) ? esc_textarea($_POST['cra_comments']) : ''; ?></textarea>
            </p>

            <!-- Website -->
            <p>
                <label for="cra_website">Website</label>
                <input type="text" id="cra_website" name="cra_website"
                    placeholder="Enter your website"
                    value="<?php echo isset($_POST['cra_website']) ? esc_attr($_POST['cra_website']) : ''; ?>">
            </p>

            <!-- Email -->
            <p>
                <label for="cra_email">Email <span style="color:red;">*</span></label>
                <input type="email" id="cra_email" name="cra_email" required placeholder="Enter your email address"
                    value="<?php echo isset($_POST['cra_email']) ? esc_attr($_POST['cra_email']) : ''; ?>">
            </p>

            <!-- Password -->
            <p>
                <label for="cra_password">Password <span style="color:red;">*</span></label>
                <input type="password" id="cra_password" name="cra_password" required placeholder="Create your password">
            </p>

            <!-- Confirm Password -->
            <p>
                <label for="cra_password_confirm">Confirm Password <span style="color:red;">*</span></label>
                <input type="password" id="cra_password_confirm" name="cra_password_confirm" required placeholder="Confirm your password">
            </p>

            <?php wp_nonce_field('cra_register_form', 'cra_nonce'); ?>

            <p class="submit-wrapper">
                <button class="cra_submit_btn" type="submit" name="cra_submit" value="1">Register</button>
            </p>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Username allowed characters enforcement
                var usernameInput = document.querySelector('#cra-register-form input[name="cra_username"]');
                if (usernameInput) {
                    usernameInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^A-Za-z0-9._]/g, '');
                    });
                }

                // Auto-fill delivery address if checkbox checked
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
