<?php
if (!defined('ABSPATH')) exit;

// Add submenu under Users
add_action('admin_menu', function () {
    add_users_page(
        'Registration Approvals',
        'Registration Approvals',
        'list_users',
        'registration-approvals',
        'cra_render_admin_list'
    );
});

function cra_render_admin_list()
{
?>
    <div class="wrap">
        <h1>Pending User Registrations</h1>
        <?php
        $args = [
            'meta_key'   => 'cra_approval_status',
            'meta_value' => 'pending',
            'role'       => 'pending', // if such a role is assigned
        ];
        $pending_users = get_users($args);

        if (empty($pending_users)) {
            echo "<p>No pending registrations.</p>";
            return;
        }
        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Company Number</th>
                    <th>VAT</th>
                    <th>Position in Co.</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_users as $user): ?>
                    <tr>
                        <td><?php echo esc_html($user->user_login); ?></td>
                        <td><?php echo esc_html($user->first_name . " " . $user->last_name); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html(get_user_meta($user->ID, 'company_name', true)); ?></td>
                        <td><?php echo esc_html(get_user_meta($user->ID, 'company_number', true)); ?></td>
                        <td><?php echo esc_html(get_user_meta($user->ID, 'vat_number', true)); ?></td>
                        <td><?php echo esc_html(get_user_meta($user->ID, 'position_in_company', true)); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('cra_approval_action', 'cra_approval_nonce'); ?>
                                <input type="hidden" name="cra_user_id" value="<?php echo intval($user->ID); ?>">
                                <button name="cra_approve_user" class="button button-primary" value="1">Approve</button>
                                <button name="cra_reject_user" class="button button-secondary" value="1" onclick="return confirm('Reject this registration?');">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}

// Helper to assemble all registration info (for emails)
function cra_get_registration_fields($user_id)
{
    $user = get_userdata($user_id);
    $fields = [
        'First Name'          => get_user_meta($user_id, 'first_name', true),
        'Last Name'           => get_user_meta($user_id, 'last_name', true),
        'Username'            => $user ? $user->user_login : '',
        'Email'               => $user ? $user->user_email : '',
        'Company Name'        => get_user_meta($user_id, 'company_name', true),
        'Company Number'      => get_user_meta($user_id, 'company_number', true),
        'VAT Number'          => get_user_meta($user_id, 'vat_number', true),
        'Position in Company' => get_user_meta($user_id, 'position_in_company', true),
        'Phone'               => get_user_meta($user_id, 'phone', true),
        'Company Address'     => get_user_meta($user_id, 'company_address', true),
        'Delivery Address'    => get_user_meta($user_id, 'delivery_address', true),
        'Website'             => get_user_meta($user_id, 'website', true),
        'Comments/Notes'      => get_user_meta($user_id, 'comments', true),
    ];
    $out = "";
    foreach ($fields as $label => $val) {
        $out .= sprintf("%-18s: %s\n", $label, $val ? $val : '-');
    }
    return $out;
}

// Process approval/rejection POST
add_action('admin_init', function () {
    if (!isset($_POST['cra_approval_nonce']) || !wp_verify_nonce($_POST['cra_approval_nonce'], 'cra_approval_action')) return;
    if (current_user_can('edit_users') && !empty($_POST['cra_user_id'])) {
        $user_id = intval($_POST['cra_user_id']);
        if (!empty($_POST['cra_approve_user'])) {
            cra_approve_user($user_id);
            cra_send_user_notification($user_id, 'approved');
        }
        if (!empty($_POST['cra_reject_user'])) {
            cra_send_user_notification($user_id, 'rejected');
            cra_reject_user($user_id);
        }
        wp_redirect(admin_url('users.php?page=registration-approvals&action=done'));
        exit;
    }
});

function cra_approve_user($user_id)
{
    update_user_meta($user_id, 'cra_approval_status', 'approved');
    $user = new WP_User($user_id);
    $user->set_role('customer');
}
function cra_reject_user($user_id)
{
    update_user_meta($user_id, 'cra_approval_status', 'rejected');
    $user = get_userdata($user_id);
    if ($user) wp_delete_user($user_id);
}


// Dynamically get the domain for noreply
$domain  = parse_url(home_url(), PHP_URL_HOST);
$from    = 'noreply@' . $domain; // noreply@yourdomain.com

// Set From header
$headers = array(
    'From: Wholesale Portal <' . $from . '>'
);

// Optionally: add CC/BCC
// $headers[] = 'Cc: someone@yourdomain.com';
// $headers[] = 'Bcc: another@yourdomain.com';


// Send user notification (approval or rejection)
function cra_send_user_notification($user_id, $status)
{
    $user = get_userdata($user_id);
    if (!$user) return;
    $to = $user->user_email;
    // Dynamically get the domain for noreply
    $domain  = parse_url(home_url(), PHP_URL_HOST);
    $from    = 'noreply@' . $domain; // noreply@yourdomain.com

    // Set From header
    $headers = array(
        'From: ' . get_bloginfo('name') . '<' . $from . '>'
    );
    $subject = '';
    $message = '';

    switch ($status) {
        case 'approved':
            $subject = 'Your wholesale registration is approved';
            $login_url = wp_login_url();
            $account_url = wc_get_account_endpoint_url('dashboard'); // WooCommerce my-account
            $message = "Dear {$user->display_name},\n\n"
                . "Your registration has been approved. You may now log in below:\n\n"
                . "Login: $login_url\n"
                . "My Account: $account_url\n\n"
                . "Thank you for joining " . get_bloginfo('name') . "!";
            break;
        case 'rejected':
            $subject = 'Your wholesale registration was rejected';
            $message = "Dear {$user->display_name},\n\nUnfortunately, your registration was not approved. Please contact support for more info.\n";
            break;
    }
    wp_mail($to, $subject, $message, $headers);
}

// Send admin and user registration notification after meta is saved
function cra_send_new_registration_notification($user_id, $extra = [])
{
    $user = get_userdata($user_id);
    if (!$user) return;

    $fields = cra_get_registration_fields($user_id);

    // Prepare API data for admin (if passed)
    $api_section = '';
    if (!empty($extra['company_api'])) {
        $api = $extra['company_api'];
        $api_section .= "\nCompany API Verification Result:\n";
        foreach ($api as $k => $v) {
            $api_section .= ucfirst(str_replace('_', ' ', $k)) . ': ' . $v . "\n";
        }
        if (isset($extra['api_status'])) {
            $api_section .= "Auto-approval Result: " . $extra['api_status'] . "\n";
        }
    }
    // Add VAT API raw result for admin only
    if (!empty($extra['vat_api']) && is_array($extra['vat_api'])) {
        $vat = $extra['vat_api'];
        $api_section .= "\nVAT API Verification Result:\n";
        foreach ($vat as $k => $v) {
            $api_section .= ucfirst(str_replace('_', ' ', $k)) . ': ' . (is_scalar($v) ? $v : json_encode($v)) . "\n";
        }
    }

    // Status line
    $status_line = "Status: " . (isset($extra['approval_status']) ? $extra['approval_status'] : 'pending');

    // Dynamically get the domain for noreply
    $domain = parse_url(home_url(), PHP_URL_HOST);
    $from = 'noreply@' . $domain;

    // Set From header
    $headers = array(
        'From: ' . get_bloginfo('name') . ' <' . $from . '>'
    );

    // 1. Send to Admin (always)
    $admin_email = get_option('admin_email');
    $approval_url = admin_url('users.php?page=registration-approvals');
    $subject_admin = 'New wholesale registration pending approval';
    $message_admin = "$status_line\n\nA new user registration is pending review:\n\n$fields$api_section\n\nApprove or reject this registration here:\n$approval_url";
    wp_mail($admin_email, $subject_admin, $message_admin, $headers);

    // 2. Send to User - conditional content based on approval_status
    $blogname = get_option('blogname') ?: 'Wholesale Portal';

    if (!empty($extra['approval_status']) && $extra['approval_status'] === 'approved') {
        $login_url = wp_login_url();
        $account_url = wc_get_account_endpoint_url('dashboard'); // WooCommerce my-account
        $subject_user = 'Your wholesale registration is approved';
        $message_user = "Dear {$user->display_name},\n\n".
            "Thank you for registering. Your account has been auto-approved. You may now log in using the following links:\n\n".
            "Login: $login_url\n".
            "My Account: $account_url\n\n".
            "We look forward to doing business with you.\n\n".
            "Best regards,\n$blogname";
    } else {
        // Pending approval email (existing format)
        $subject_user = 'Thank you for registering with ' . $blogname;
        $message_user = "Dear {$user->display_name},\n\n".
            "Thank you for your registration. Please find a copy of your submitted information below:\n\n".
            $fields .
            "\n\nWe will review your application and notify you when your account is activated.\n\n".
            "Best regards,\n$blogname";
    }

    wp_mail($user->user_email, $subject_user, $message_user, $headers);
}

