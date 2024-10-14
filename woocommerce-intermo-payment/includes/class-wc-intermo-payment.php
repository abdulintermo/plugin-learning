<?php

class WC_Gateway_Intermo extends WC_Payment_Gateway
{
    // Declare properties
    public $sandbox_auth_key;
    public $sandbox_public_key;
    public $sandbox_secret_key;
    public $live_auth_key;
    public $live_public_key;
    public $live_secret_key;
    public $payment_mode;

    public function __construct()
    {
        $this->id                 = 'intermo';
        $this->method_title       = __('Intermo Payment Gateway', 'wc-gateway-intermo');
        $this->method_description = __('Allows payments using Intermo Payment Gateway.', 'wc-gateway-intermo');

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Assign settings to variables
        $this->enabled            = $this->get_option('enabled');
        $this->payment_mode       = $this->get_option('payment_mode');
        $this->sandbox_auth_key   = $this->get_option('sandbox_auth_key');
        $this->sandbox_public_key = $this->get_option('sandbox_public_key');
        $this->sandbox_secret_key = $this->get_option('sandbox_secret_key');
        $this->live_auth_key      = $this->get_option('live_auth_key');
        $this->live_public_key    = $this->get_option('live_public_key');
        $this->live_secret_key    = $this->get_option('live_secret_key');

        // Save settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Define the settings fields
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'wc-gateway-intermo'),
                'type'    => 'checkbox',
                'label'   => __('Enable Intermo Payment Gateway', 'wc-gateway-intermo'),
                'default' => 'yes',
            ),
            'payment_mode' => array(
                'title'       => __('Payment Mode', 'wc-gateway-intermo'),
                'type'        => 'select',
                'description' => __('Select Sandbox for testing or Live for production.', 'wc-gateway-intermo'),
                'default'     => 'sandbox',
                'desc_tip'    => true,
                'options'     => array(
                    'sandbox' => __('Sandbox', 'wc-gateway-intermo'),
                    'live'    => __('Live', 'wc-gateway-intermo'),
                ),
            ),
            // Sandbox Keys
            'sandbox_auth_key' => array(
                'title'       => __('Sandbox Auth Key', 'wc-gateway-intermo'),
                'type'        => 'text',
                'description' => __('Enter your sandbox Auth key.', 'wc-gateway-intermo'),
                'default'     => '',
                'desc_tip'    => true,
                'class'       => 'sandbox_field',
            ),
            'sandbox_public_key' => array(
                'title'       => __('Sandbox Public Key', 'wc-gateway-intermo'),
                'type'        => 'text',
                'description' => __('Enter your sandbox Public key.', 'wc-gateway-intermo'),
                'default'     => '',
                'desc_tip'    => true,
                'class'       => 'sandbox_field',
            ),
            'sandbox_secret_key' => array(
                'title'       => __('Sandbox Secret Key', 'wc-gateway-intermo'),
                'type'        => 'text',
                'description' => __('Enter your sandbox Secret key.', 'wc-gateway-intermo'),
                'default'     => '',
                'desc_tip'    => true,
                'class'       => 'sandbox_field',
            ),
            // Live Keys
            'live_auth_key' => array(
                'title'       => __('Live Auth Key', 'wc-gateway-intermo'),
                'type'        => 'text',
                'description' => __('Enter your live Auth key.', 'wc-gateway-intermo'),
                'default'     => '',
                'desc_tip'    => true,
                'class'       => 'live_field',
            ),
            'live_public_key' => array(
                'title'       => __('Live Public Key', 'wc-gateway-intermo'),
                'type'        => 'text',
                'description' => __('Enter your live Public key.', 'wc-gateway-intermo'),
                'default'     => '',
                'desc_tip'    => true,
                'class'       => 'live_field',
            ),
            'live_secret_key' => array(
                'title'       => __('Live Secret Key', 'wc-gateway-intermo'),
                'type'        => 'text',
                'description' => __('Enter your live Secret key.', 'wc-gateway-intermo'),
                'default'     => '',
                'desc_tip'    => true,
                'class'       => 'live_field',
            ),
        );
    }

    /**
     * Output the settings form on the admin page
     */
    public function admin_options()
    {
        ?>
<h2><?php echo esc_html($this->method_title); ?></h2>
<p><?php echo esc_html($this->method_description); ?></p>
<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		function toggle_fields() {
			var mode = $('#woocommerce_intermo_payment_mode').val();
			if (mode === 'sandbox') {
				$('.sandbox_field').closest('tr').show();
				$('.live_field').closest('tr').hide();
			} else {
				$('.sandbox_field').closest('tr').hide();
				$('.live_field').closest('tr').show();
			}
		}
		toggle_fields();
		$('#woocommerce_intermo_payment_mode').change(function() {
			toggle_fields();
		});
	});
</script>
<?php
    }

    /**
     * Process and save the options in the admin page
     */
    public function process_admin_options()
    {
        parent::process_admin_options();  // This will save the settings
    }

    /**
     * Process the payment and return the result
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // Determine which keys to use based on the payment mode
        if ($this->payment_mode === 'sandbox') {
            $auth_key   = $this->sandbox_auth_key;
            $public_key = $this->sandbox_public_key;
            $secret_key = $this->sandbox_secret_key;
        } else {
            $auth_key   = $this->live_auth_key;
            $public_key = $this->live_public_key;
            $secret_key = $this->live_secret_key;
        }

        // TODO: Use these keys to process the payment with Intermo's API

        // For now, we'll mark the order as on-hold
        $order->update_status('on-hold', __('Awaiting payment confirmation from Intermo.', 'wc-gateway-intermo'));

        // Reduce stock levels
        wc_reduce_stock_levels($order_id);

        // Empty the cart
        WC()->cart->empty_cart();

        // Return thank you page redirect
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }
}
?>