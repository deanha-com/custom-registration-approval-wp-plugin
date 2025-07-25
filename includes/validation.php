<?php
if (!defined('ABSPATH')) exit;

function cra_handle_registration_form()
{
    $errors = [];

    // CSRF check
    if (
        !isset($_POST['cra_nonce']) ||
        !(wp_verify_nonce($_POST['cra_nonce'], 'cra_register_form') ||
            wp_verify_nonce($_POST['cra_nonce'], 'cra_multistep_register'))
    ) {
        $errors[] = 'Security check failed.';
        return ['errors' => $errors];
    }

    // Required fields
    $required = [
        'cra_first_name' => 'First Name',
        'cra_last_name' => 'Last Name',
        'cra_username' => 'Username',
        'cra_company' => 'Company Name',
        'cra_company_number' => 'Company Number',
        'cra_company_role' => 'Role in the Company',
        'cra_email' => 'Email',
        'cra_password' => 'Password',
        'cra_password_confirm' => 'Password Confirmation'
    ];
    foreach ($required as $key => $label) {
        if (empty($_POST[$key])) $errors[] = "$label is required.";
    }

    // Password confirmation
    if (!empty($_POST['cra_password']) && !empty($_POST['cra_password_confirm'])) {
        if ($_POST['cra_password'] !== $_POST['cra_password_confirm']) {
            $errors[] = "Passwords do not match.";
        }
    }

    // Username/email checks
    $username = sanitize_user($_POST['cra_username']);
    if (!preg_match('/^[A-Za-z0-9._]+$/', $username)) {
        $errors[] = "Username may only contain letters, numbers, dot (.) and underscore (_).";
    }
    $email = sanitize_email($_POST['cra_email']);

    if (username_exists($username)) $errors[] = "Username already exists.";
    if (!is_email($email)) $errors[] = "Invalid email address.";
    if (email_exists($email)) $errors[] = "Email already registered.";

    // Prepare company data
    $company_number = sanitize_text_field($_POST['cra_company_number']);
    $company_name   = sanitize_text_field($_POST['cra_company']);
    $company_valid  = cra_validate_company_number_api($company_number, $company_name);

    // Optionally validate VAT for meta
    $vat_number = !empty($_POST['cra_vat']) ? sanitize_text_field($_POST['cra_vat']) : '';
    $vat_status = '';
    if (!empty($vat_number)) {
        $vat_status = cra_check_vat_api($vat_number);
    }

    // If errors, return now
    if (!empty($errors)) {
        return ['errors' => $errors];
    }

    // Registration: create new user
    $user_id = wp_create_user($username, $_POST['cra_password'], $email);
    if (is_wp_error($user_id)) {
        $errors[] = "User registration failed.";
        return ['errors' => $errors];
    }

    // Save user meta
    update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['cra_first_name']));
    update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['cra_last_name']));
    update_user_meta($user_id, 'company_name', $company_name);
    update_user_meta($user_id, 'company_number', $company_number);
    update_user_meta($user_id, 'vat_number', $vat_number);
    update_user_meta($user_id, 'vat_status', $vat_status);
    update_user_meta($user_id, 'role_in_company', sanitize_text_field($_POST['cra_company_role']));

    // Conditional approval status and role assignment
    if ($company_valid) {
        update_user_meta($user_id, 'cra_approval_status', 'approved');
        $user = new WP_User($user_id);
        $user->set_role('customer');
        $approval_status = 'approved';
    } else {
        update_user_meta($user_id, 'cra_approval_status', 'pending');
        $user = new WP_User($user_id);
        $user->set_role('pending');
        $approval_status = 'pending';
    }

    // Optionally log user in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    // Return structured success data (no redirect)
    return [
        'success' => true,
        'approval_status' => $approval_status,
        'user_id' => $user_id,
    ];
}


function cra_validate_company_number_api($company_number, $user_company_name = '')
{
    if (empty($company_number)) return false;

    $endpoint = 'https://vatapi-kohl.vercel.app/check-company';
    $response = wp_remote_post($endpoint, [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode(['company_number' => $company_number]),
        'timeout' => 10,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (
        empty($body['company_number']) ||
        strtolower($body['company_number']) !== strtolower($company_number)
    ) {
        return false;
    }

    if (
        empty($body['company_status']) ||
        strtolower($body['company_status']) !== 'active'
    ) {
        return false;
    }

    $api_name   = strtolower(trim(preg_replace('/\s+/', ' ', $body['company_name'] ?? '')));
    $input_name = strtolower(trim(preg_replace('/\s+/', ' ', $user_company_name)));

    if (empty($input_name) || strpos($api_name, $input_name) === false) {
        return false;
    }

    return true;
}


function cra_check_vat_api($vat_number)
{
    $endpoint = 'https://vatapi-kohl.vercel.app/check-vat';
    $response = wp_remote_post($endpoint, [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode(['vat_number' => $vat_number]),
        'timeout' => 10,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return '';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return isset($body['vat_status']) ? $body['vat_status'] : '';
}
