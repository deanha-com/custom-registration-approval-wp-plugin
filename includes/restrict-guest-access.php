<?php
if (!defined('ABSPATH')) exit;

// ** Redirect any attempt to see product, shop, cart, or checkout if user is not logged in **
function cra_restrict_guest_access_redirect() {
    if (is_user_logged_in()) return;

    // List of WooCommerce pages to protect
    if (is_product() || is_shop() || is_product_category() || is_cart() || is_checkout()) {
        // Change this URL to your registration page
        $reg_url = home_url('/wholesale-access/');
        wp_redirect($reg_url);
        exit;
    }
}
add_action('template_redirect', 'cra_restrict_guest_access_redirect', 1);

// ** Hide prices and cart for guests **
function cra_hide_price_for_guest($price, $product) {
    if (!is_user_logged_in()) {
        return '<span class="cra-login-alert">Login to view price</span>';
    }
    return $price;
}
add_filter('woocommerce_get_price_html', 'cra_hide_price_for_guest', 99, 2);

// Optionally: Remove add to cart buttons for guests
function cra_remove_add_to_cart_for_guests() {
    if (!is_user_logged_in()) {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }
}
add_action('wp', 'cra_remove_add_to_cart_for_guests');

// Optionally: Hide cart and checkout page menu items for guests (works with standard Woo menu)
function cra_hide_cart_checkout_links_for_guests($items, $menu, $args) {
    if (!is_user_logged_in()) {
        $hide = ['Cart', 'Checkout'];
        foreach ($items as $key => $item) {
            if (in_array($item->title, $hide)) unset($items[$key]);
        }
    }
    return $items;
}
add_filter('wp_get_nav_menu_items', 'cra_hide_cart_checkout_links_for_guests', 10, 3);
