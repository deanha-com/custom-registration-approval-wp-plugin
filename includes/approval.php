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
                    <th>Role in Co.</th>
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
                        <td><?php echo esc_html(get_user_meta($user->ID, 'role_in_company', true)); ?></td>
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
            cra_reject_user($user_id);
            cra_send_user_notification($user_id, 'rejected');
        }
        wp_redirect(admin_url('users.php?page=registration-approvals&action=done'));
        exit;
    }
});

function cra_approve_user($user_id)
{
    update_user_meta($user_id, 'cra_approval_status', 'approved');
    $user = new WP_User($user_id);
    $user->set_role('wholesale_customer');
}
function cra_reject_user($user_id)
{
    update_user_meta($user_id, 'cra_approval_status', 'rejected');
    $user = get_userdata($user_id);
    if ($user) wp_delete_user($user_id);
}




function cra_send_user_notification($user_id, $status)
{
    $user = get_userdata($user_id);
    if (!$user) return;
    $to = $user->user_email;
    $subject = '';
    $message = '';

    switch ($status) {
        case 'approved':
            $subject = 'Your wholesale registration is approved';
            $message = "Dear {$user->display_name},\n\nYour registration has been approved. You may now log in.\n";
            break;
        case 'rejected':
            $subject = 'Your wholesale registration was rejected';
            $message = "Dear {$user->display_name},\n\nUnfortunately, your registration was not approved. Please contact support for more info.\n";
            break;
    }
    wp_mail($to, $subject, $message);
}

add_action('user_register', function ($user_id) {
    $admin_email = get_option('admin_email');
    $user = get_userdata($user_id);
    $subject = 'New wholesale registration pending approval';
    $message = "A new user registration is pending review:\n\nUsername: {$user->user_login}\nEmail: {$user->user_email}";
    wp_mail($admin_email, $subject, $message);
});



?>