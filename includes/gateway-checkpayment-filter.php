<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Rename Check Payments gateway to "Invoice Payment"
 * and restrict it to users with isOnCredit = 'yes'
 */

/** Change gateway title */
add_filter('woocommerce_gateway_title', function ($title, $gateway_id) {
    if ('cheque' === $gateway_id) {
        $title = 'Invoice Payment';
    }
    return $title;
}, 10, 2);

/** Change gateway description */
add_filter('woocommerce_gateway_description', function ($description, $gateway_id) {
    if ('cheque' === $gateway_id) {
        $description = 'You will receive an invoice after checkout; no payment required now.';
    }
    return $description;
}, 10, 2);

/** Conditionally show the gateway only for users with isOnCredit = 'yes' */
add_filter('woocommerce_available_payment_gateways', function ($available_gateways) {
    if (is_admin()) {
        return $available_gateways; // Do not filter in admin
    }

    $user_id     = get_current_user_id();
    $is_on_credit = ($user_id) ? get_user_meta($user_id, 'isOnCredit', true) : '';

    if ($is_on_credit !== 'yes' && isset($available_gateways['cheque'])) {
        unset($available_gateways['cheque']);
    }

    return $available_gateways;
});
