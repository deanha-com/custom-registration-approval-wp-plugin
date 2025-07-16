<?php
if (!defined('ABSPATH')) exit;

// Register AJAX handler
add_action('wp_ajax_validate_company_number', 'cra_ajax_validate_company_number');
add_action('wp_ajax_nopriv_validate_company_number', 'cra_ajax_validate_company_number');

function cra_ajax_validate_company_number()
{
    check_ajax_referer('cra_multistep_register', 'security');

    $company_number = sanitize_text_field($_POST['company_number']);
    if (empty($company_number)) {
        wp_send_json_error('Missing Company Number.');
    }

    $response = wp_remote_post('https://vatapi-kohl.vercel.app/check-company', [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode(['company_number' => $company_number]),
        'timeout' => 10,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
        wp_send_json_error('Validation service not available.');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($body['company_number']) && $body['company_number'] === $company_number) {
        wp_send_json_success('Company Number Valid ✔️');
    }

    wp_send_json_error('Company Number is invalid.');
}

add_action('wp_enqueue_scripts', 'cra_enqueue_multistep_assets');
