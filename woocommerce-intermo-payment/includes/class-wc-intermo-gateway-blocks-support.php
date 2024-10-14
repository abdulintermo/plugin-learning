<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

final class WC_Intermo_Gateway_Blocks_Support extends AbstractPaymentMethodType
{
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'intermo';

    /**
     * Payment gateway settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * Initializes the payment method type.
     */
    public function initialize()
    {
        // Get payment gateway settings
        $this->settings = get_option("woocommerce_{$this->name}._settings", array());

        // Add action to handle failed payments
        add_action('woocommerce_rest_checkout_process_payment_with_context', array( $this, 'failed_payment_notice' ), 8, 2);
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active()
    {
        return ! empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles()
    {

        
        $script_asset_path = plugin_dir_path(__FILE__) . 'build/index.asset.php';
        $script_asset      = file_exists($script_asset_path)
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version'      => '1.0.0',
            );

        $script_url = plugin_dir_url(__FILE__) . 'build/index.js';
        wp_register_script(
            'wc-intermo-blocks-integration',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        return array( 'wc-intermo-blocks-integration' );
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data()
    {
        $payment_gateways_class = WC()->payment_gateways();
        $payment_gateways       = $payment_gateways_class->payment_gateways();
        $gateway                = $payment_gateways[ $this->name ];

        return array(
            'title'       => $this->get_setting('title'),
            'description' => $this->get_setting('description'),
            // 'icon'        => plugin_dir_url(__FILE__) . 'assets/img/intermo.svg',
            'supports'    => array_filter($gateway->supports, array( $gateway, 'supports' )),
        );
    }

    /**
     * Add failed payment notice to the payment details.
     *
     * @param PaymentContext $context Holds context for the payment.
     * @param PaymentResult  $result  Result object for the payment.
     */
    public function failed_payment_notice(PaymentContext $context, PaymentResult &$result)
    {
        if ($this->name === $context->payment_method) {
            add_action(
                'wc_gateway_' . $this->name . '_process_payment_error',
                function ($failed_notice) use (&$result) {
                    $payment_details                 = $result->get_payment_details();
                    $payment_details['errorMessage'] = wp_strip_all_tags($failed_notice);
                    $result->set_payment_details($payment_details);
                }
            );
        }
    }

    // /**
    //  * Get a specific setting value.
    //  *
    //  * @param string $key Setting key.
    //  *
    //  * @return mixed
    //  */
    // private function get_setting($key)
    // {
    //     return isset($this->settings[ $key ]) ? $this->settings[ $key ] : '';
    // }
}
