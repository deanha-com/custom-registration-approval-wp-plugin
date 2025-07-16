<?php
if (!defined('ABSPATH')) exit;

function cra_restricted_screen_shortcode()
{
    ob_start();
    $register_url = home_url('/register/');
?>

    <style>
        .cra-split-screen {
            display: flex;
            max-width: 960px;
            margin: 50px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .cra-left,
        .cra-right {
            width: 50%;
            padding: 40px;
            box-sizing: border-box;
        }

        .cra-left {
            background: #f7f7f7;
            border-right: 1px solid #eee;
        }

        .cra-right {
            background: #fff;
        }

        .cra-right h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .cra-benefits {
            margin: 20px 0;
        }

        .cra-benefits li {
            list-style: none;
            margin-bottom: 10px;
        }

        .cra-register {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #2ea7e0;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
        }

        .woocommerce-error {
            color: red;
            margin-bottom: 10px;
            font-size: 14px;
        }


        #cra-login-form input {
            width: -webkit-fill-available;
        }

        #cra-login-form input[type=checkbox], #cra-login-form input[type=radio] {
            width: auto;
        }
    </style>
    <div class="cra-split-screen woocommerce">
        <div class="cra-left">
            <h2>Log in to your account</h2>
            <?php
            $login_args = [
                'echo'           => true,
                'redirect'       => wc_get_page_permalink('shop'),
                'form_id'        => 'cra-login-form',
                'label_username' => __('Email or Username'),
                'label_password' => __('Password'),
                'label_remember' => __('Remember Me'),
                'label_log_in'   => __('Login'),
                'remember'       => true
            ];
            wp_login_form($login_args);
            ?>
        </div>
        <div class="cra-right">
            <h2>Wholesale Access Required</h2>
            <ul class="cra-benefits">
                <li>📦 Access bulk pricing &amp; exclusive discounts</li>
                <li>🚀 Priority shipping options</li>
                <li>✅ Easy order management</li>
            </ul>
            <a class="cra-register" href="<?php echo esc_url($register_url); ?>">Register Now</a>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('cra_restricted_screen', 'cra_restricted_screen_shortcode');
