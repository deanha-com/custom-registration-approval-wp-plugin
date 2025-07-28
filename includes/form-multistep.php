<?php
if (!defined('ABSPATH')) exit;


// Register shortcode
add_shortcode('cra_multistep_registration_form', 'cra_render_multistep_registration_form');
// add_action('wp_enqueue_scripts', 'cra_enqueue_multistep_assets');

// function cra_enqueue_multistep_assets() {
//     // Adjust paths as needed
//     wp_enqueue_script('cra-multistep-js', plugins_url('../assets/multistep.js', __FILE__), ['jquery'], null, true);
//     wp_enqueue_style('cra-multistep-css', plugins_url('../assets/multistep.css', __FILE__));
// }

function cra_render_multistep_registration_form()
{
    ob_start();

    $errors = [];
    $success = false;
    $approval_status = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $result = cra_handle_registration_form();

        if (isset($result['errors']) && !empty($result['errors'])) {
            $errors = $result['errors'];
        } elseif (isset($result['success']) && $result['success'] === true) {
            $success = true;
            $approval_status = $result['approval_status'] ?? '';
        }
    }

    // Display success message if registration successful
    if ($success) {
?>
        <div class="cra-success-message" style="padding:20px; border: 1px solid <?php echo ($approval_status === 'approved') ? '#4caf50' : '#31708f'; ?>; background: <?php echo ($approval_status === 'approved') ? '#dff0d8' : '#d9edf7'; ?>; color: <?php echo ($approval_status === 'approved') ? '#3c763d' : '#31708f'; ?>;">
            <h2>
                <?php echo ($approval_status === 'approved') ? 'Registration successful!' : 'Registration received'; ?>
            </h2>
            <p>
                <?php
                if ($approval_status === 'approved') {
                ?>
                    Your account has been auto-approved. You may now <a href="<?php echo esc_url(wp_login_url()); ?>">log in here</a>.
                <?php
                } else {
                ?>
                    Your application will be reviewed. We will notify you by email once your account is activated.
                <?php
                }
                ?>
            </p>
        </div>
    <?php
        // Do not show form on success
        return ob_get_clean();
    }

    // Enqueue assets
    cra_enqueue_multistep_assets();

    // Inline CSS for multi-step grid and responsive layout
    ?>
    <style>
        #cra-multistep-form fieldset {
            border: none;
            padding: 2.5rem;
            display: none;
        }

        #cra-multistep-form fieldset.active {
            display: block;
        }

        /* Responsive two column grid layout for inputs inside each fieldset */
        .cra-fieldset-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            grid-gap: 1.2rem 1.5rem;
            margin-top: 1rem;
            margin-bottom: 2rem;
        }

        .cra-fieldset-grid p {
            margin: 0;
        }

        .cra-fieldset-grid label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .cra-fieldset-grid input[type="text"],
        .cra-fieldset-grid input[type="email"],
        .cra-fieldset-grid input[type="password"],
        .cra-fieldset-grid textarea {
            width: 100%;
            padding: 0.4rem 0.6rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .cra-fieldset-grid textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Full width on all columns for textarea, checkbox label */
        .cra-fieldset-grid textarea,
        .checkbox-label {
            grid-column: 1 / -1;
        }

        /* Submit and navigation buttons */
        #cra-multistep-form .action-button {
            background-color: #0073aa;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 0.5rem;
        }

        #cra-multistep-form .action-button:hover {
            background-color: #005177;
        }

        #cra-multistep-form #cra-progressbar {
            margin-bottom: 2rem;
            overflow: hidden;
            counter-reset: step;
            display: flex;
            justify-content: space-between;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 0;
        }

        #cra-multistep-form #cra-progressbar li {
            list-style-type: none;
            color: gray;
            text-transform: uppercase;
            font-size: 0.75rem;
            width: 33.3%;
            position: relative;
            text-align: center;
            cursor: default;
        }

        #cra-multistep-form #cra-progressbar li.active {
            color: #0073aa;
            font-weight: 700;
        }

        #cra-multistep-form #cra-progressbar li:before {
            content: counter(step);
            counter-increment: step;
            width: 24px;
            height: 24px;
            line-height: 24px;
            border: 2px solid gray;
            display: block;
            text-align: center;
            margin: 0 auto 10px auto;
            border-radius: 50%;
            background: white;
        }

        #cra-multistep-form #cra-progressbar li.active:before {
            border-color: #0073aa;
        }

        /* Checkbox label styling */
        label.checkbox-label {
            display: flex;
            align-items: center;
            font-weight: normal;
            cursor: pointer;
            user-select: none;
        }

        label.checkbox-label input[type="checkbox"] {
            margin-right: 10px;
            width: auto;
        }

        /* Responsive modifications */
        @media (max-width: 600px) {
            .cra-fieldset-grid {
                grid-template-columns: 1fr !important;
            }

            #cra-multistep-form #cra-progressbar li {
                font-size: 0.65rem;
            }
        }
    </style>

    <?php
    // Output any errors
    if (!empty($errors)) {
        echo '<ul class="cra-errors">';
        foreach ($errors as $err) {
            echo '<li>' . esc_html($err) . '</li>';
        }
        echo '</ul>';
    }
    ?>


    <form id="cra-multistep-form" method="post" novalidate>
        <?php wp_nonce_field('cra_multistep_register', 'cra_nonce'); ?>

        <!-- Progress bar -->
        <ul id="cra-progressbar">
            <li class="active">Account</li>
            <li>Company</li>
            <li>Review</li>
        </ul>

        <!-- Step 1: Account Information -->
        <fieldset class="active">
            <h2>Step 1: Account Information</h2>
            <div class="cra-fieldset-grid">
                <p>
                    <label for="cra_first_name">First Name <span style="color:red;">*</span></label>
                    <input type="text" id="cra_first_name" name="cra_first_name" required placeholder="Enter your first name"
                        value="<?php echo isset($_POST['cra_first_name']) ? esc_attr($_POST['cra_first_name']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_last_name">Last Name <span style="color:red;">*</span></label>
                    <input type="text" id="cra_last_name" name="cra_last_name" required placeholder="Enter your last name"
                        value="<?php echo isset($_POST['cra_last_name']) ? esc_attr($_POST['cra_last_name']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_email">Email <span style="color:red;">*</span></label>
                    <input type="email" id="cra_email" name="cra_email" required placeholder="Enter your email address"
                        value="<?php echo isset($_POST['cra_email']) ? esc_attr($_POST['cra_email']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_username">Username <span style="color:red;">*</span></label>
                    <input type="text" id="cra_username" name="cra_username" required minlength="4" maxlength="30"
                        placeholder="Allowed: letters, numbers, dot (.), underscore (_) only"
                        pattern="^[A-Za-z0-9._]+$"
                        title="Username may contain only letters, numbers, dot (.), and underscore (_)."
                        value="<?php echo isset($_POST['cra_username']) ? esc_attr($_POST['cra_username']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_password">Password <span style="color:red;">*</span></label>
                    <input type="password" id="cra_password" name="cra_password" required placeholder="Create your password">
                </p>

                <p>
                    <label for="cra_password_confirm">Confirm Password <span style="color:red;">*</span></label>
                    <input type="password" id="cra_password_confirm" name="cra_password_confirm" required placeholder="Confirm your password">
                </p>
            </div>

            <input type="button" name="next" class="next action-button" value="Next" />
        </fieldset>

        <!-- Step 2: Company Information -->
        <fieldset>
            <h2>Step 2: Company Information</h2>
            <div class="cra-fieldset-grid">
                <p>
                    <label for="cra_company">Company Name <span style="color:red;">*</span></label>
                    <input type="text" id="cra_company" name="cra_company" required placeholder="Enter your company name"
                        value="<?php echo isset($_POST['cra_company']) ? esc_attr($_POST['cra_company']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_company_number">Company Number <span style="color:red;">*</span></label>
                    <input type="text" id="cra_company_number" name="cra_company_number" required placeholder="Enter your company number"
                        value="<?php echo isset($_POST['cra_company_number']) ? esc_attr($_POST['cra_company_number']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_vat">VAT Number</label>
                    <input type="text" id="cra_vat" name="cra_vat" placeholder="VAT number"
                        value="<?php echo isset($_POST['cra_vat']) ? esc_attr($_POST['cra_vat']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_position_in_company">Position in the Company <span style="color:red;">*</span></label>
                    <input type="text" id="cra_position_in_company" name="cra_position_in_company" required placeholder="Enter your position in company"
                        value="<?php echo isset($_POST['cra_position_in_company']) ? esc_attr($_POST['cra_position_in_company']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_phone">Phone Number <span style="color:red;">*</span></label>
                    <input type="text" id="cra_phone" name="cra_phone" required placeholder="Enter your phone number"
                        value="<?php echo isset($_POST['cra_phone']) ? esc_attr($_POST['cra_phone']) : ''; ?>">
                </p>

                <p>
                    <label for="cra_website">Website</label>
                    <input type="text" id="cra_website" name="cra_website"
                        placeholder="Enter your website"
                        value="<?php echo isset($_POST['cra_website']) ? esc_attr($_POST['cra_website']) : ''; ?>">
                </p>

                <p style="grid-column: 1 / -1;">
                    <label for="cra_company_address">Company Address <span style="color:red;">*</span></label>
                    <input type="text" id="cra_company_address" name="cra_company_address" required placeholder="Enter your company address"
                        value="<?php echo isset($_POST['cra_company_address']) ? esc_attr($_POST['cra_company_address']) : ''; ?>">
                </p>

                <p style="grid-column: 1 / -1; ">
                    <label for="cra_delivery_address">Goods Delivery Address <span style="color:red;">*</span></label>
                    <input type="text" id="cra_delivery_address" name="cra_delivery_address" required placeholder="Enter goods delivery address"
                        value="<?php echo isset($_POST['cra_delivery_address']) ? esc_attr($_POST['cra_delivery_address']) : ''; ?>">
                </p>

                <label for="cra_same_address" class="checkbox-label" style="justify-content: flex-end; position: relative; top: -1rem;">
                    <input type="checkbox" id="cra_same_address" style="width: auto; margin: 8px; justify-content: flex-end;" />
                    <span>Same as company address</span>
                </label>



                <p class="full-width" style="grid-column: 1 / -1;">
                    <label for="cra_comments">Other Comments / Notes</label>
                    <textarea id="cra_comments" name="cra_comments" rows="4" placeholder="Write anything that might be useful for us"><?php echo isset($_POST['cra_comments']) ? esc_textarea($_POST['cra_comments']) : ''; ?></textarea>
                </p>
            </div>
            <input type="button" name="previous" class="previous action-button" value="Back" />
            <input type="button" name="next" class="next action-button" value="Next" />
        </fieldset>

        <!-- Step 3: Review & Submit -->
        <fieldset>
            <h2>Step 3: Review & Submit</h2>
            <div id="cra-review-summary" style="background-color: #fafafa;"></div>
            <input type="button" name="previous" class="previous action-button" value="Back" />
            <input type="submit" name="submit" class="submit action-button" value="Register" />
        </fieldset>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Username allowed characters enforcement
            var usernameInput = document.querySelector('#cra-multistep-form input[name="cra_username"]');
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

    <script>
        // Multi-step form navigation & review population
        (function() {
            var current_fs, next_fs, previous_fs; //fieldsets
            var opacity;
            var current = 0;
            var steps = document.querySelectorAll("#cra-multistep-form fieldset");
            var progressbarItems = document.querySelectorAll("#cra-progressbar li");

            function showStep(n) {
                steps.forEach(function(fs, index) {
                    fs.classList.toggle('active', index === n);
                    progressbarItems[index].classList.toggle('active', index <= n);
                });
                current = n;
                updateReview();
            }

            function updateReview() {
                // Populate review summary on step 3
                if (current === steps.length - 1) {
                    var summaryDiv = document.getElementById('cra-review-summary');
                    var fields = [
                        'cra_first_name', 'cra_last_name', 'cra_email', 'cra_username',
                        'cra_company', 'cra_company_number', 'cra_vat', 'cra_position_in_company',
                        'cra_phone', 'cra_company_address', 'cra_delivery_address', 'cra_website',
                        'cra_comments'
                    ];
                    var html = '<h3>Please review your submission</h3><table style="width:100%; border-collapse: collapse;">';
                    fields.forEach(function(field) {
                        var el = document.querySelector('[name="' + field + '"]');
                        if (el) {
                            var label = el.previousElementSibling ? el.previousElementSibling.innerText : field;
                            var val = el.value ? el.value : '-';
                            html += '<tr><td style="border:1px solid #ddd; padding:6px; font-weight:700; min-width: 300px;">' + label + '</td><td style="border:1px solid #ddd; padding:6px;">' + val + '</td></tr>';
                        }
                    });
                    html += '</table>';
                    summaryDiv.innerHTML = html;
                }
            }

            document.querySelectorAll('#cra-multistep-form .next').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (!validateStep(current))
                        return;
                    if (current < steps.length - 1) {
                        showStep(current + 1);
                    }
                });
            });

            document.querySelectorAll('#cra-multistep-form .previous').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (current > 0) {
                        showStep(current - 1);
                    }
                });
            });

            function validateStep(stepIndex) {
                var valid = true;
                var inputs = steps[stepIndex].querySelectorAll('input[required], textarea[required]');
                inputs.forEach(function(input) {
                    if (!input.value.trim()) {
                        alert('Please fill the required field: ' + (input.previousElementSibling ? input.previousElementSibling.innerText : input.name));
                        valid = false;
                        // You can add better UI feedback here instead of alert for UX
                    }
                });
                // Additional client-side validation can go here as needed
                return valid;
            }

            showStep(0); // initialize the form
        })();
    </script>

<?php

    return ob_get_clean();
}
