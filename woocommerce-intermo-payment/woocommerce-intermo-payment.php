<?php
/*
Plugin Name: WooCommerce Intermo Payment Gateway
Plugin URI: https://intermo.net/
Description: Adds Intermo payment option to WooCommerce.
Version: 1.1.0
Author: Intermo Payment Gateway Team
Author URI: https://intermo.net/
Text Domain: wc-gateway-intermo
*/

if (! defined('ABSPATH')) {
    exit("Can't access this file directly."); // Exit if accessed directly
}


// Define plugin constants
define('INTERMO_PAYEMNT_GATEWAY_VERSION', '1.1.0');
define('INTERMO_PAYEMNT_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('INTERMO_PAYEMNT_GATEWAY_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Add the gateway to WooCommerce
function wc_intermo_add_to_gateways($gateways)
{
    $gateways[] = 'WC_Gateway_Intermo';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wc_intermo_add_to_gateways');

// Load the Gateway class
add_action('plugins_loaded', 'wc_intermo_gateway_init');
function wc_intermo_gateway_init()
{
    if (class_exists('WC_Payment_Gateway')) {
        include_once INTERMO_PAYEMNT_GATEWAY_PLUGIN_PATH . 'includes/class-wc-intermo-payment.php';
    }
}

// Add settings link to the plugins page
function wc_intermo_gateway_plugin_links($links)
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=intermo') . '">' . __('Settings', 'wc-gateway-intermo') . '</a>',
    );
    return array_merge($plugin_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_intermo_gateway_plugin_links');



// // Add the gateway to WooCommerce
// function wc_intermo_add_to_gateways($gateways)
// {
//     $gateways[] = 'WC_Gateway_Intermo';
//     return $gateways;
// }

// define("INTERMO_PAYEMNT_GATEWAY_VERSION","0.1.1");
// define("INTERMO_PAYEMNT_GATEWAY_PLUGIN_URL",plugin_dir_url(__FILE__));
// define("INTERMO_PAYEMNT_GATEWAY_PLUGIN_PATH",plugin_dir_path(__FILE__));

// //ADD ACTION TO LOAD INIT FUNCTION
// add_action('plugins_loaded','wc_intermo_gateway_init');


// // Include the gateway class
// function wc_intermo_gateway_init()
// {
//     if (class_exists('WC_Payment_Gateway')) {

//         add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_intermo_gateway_plugin_links');
//         add_action('admin_notices','intermo_payment_gateway_settings');

//     } else{
//         exit("Files are not proper");
//     }
// }


// // Add plugin action links
// function wc_intermo_gateway_plugin_links($links)
// {
//     $plugin_links = array(
//         '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=intermo') . '">' . __('Settings', 'wc-gateway-intermo') . '</a>',
//     );
//     return array_merge($plugin_links, $links);
// }


/**
 * Registers WooCommerce Blocks integration.
 */
function intermo_gateway_woocommerce_block_support()
{

    if (class_exists(\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType::class)) {

        require_once __DIR__ . '/includes/class-wc-intermo-gateway-blocks-support.php';

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            static function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                $payment_method_registry->register(new WC_intermo_Gateway_Blocks_Support());
            }
        );
    }
}
add_action('woocommerce_blocks_loaded', 'intermo_gateway_woocommerce_block_support');
