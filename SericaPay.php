<?php
/**
 * WC SericaPay Gateway Class.
 * Built the SericaPay method.
 */
class WC_SericaPay extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @return void
     */
    public function __construct() {
        global $woocommerce;

        $this->id             = 'SericaPay';
        $this->icon           = apply_filters( 'woocommerce_SericaPay_icon', '' );
        $this->has_fields     = false;
        $this->method_title   = __( 'SericaPay', 'SericaPay' );

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Define user set variables.
        $this->title          = $this->settings['title'];
        $this->merchantId     = $this->settings['merchantId'];
        $this->description    = $this->settings['description'];
        $this->instructions       = $this->get_option( 'instructions' );
        $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );

        // Actions.
        if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
        else
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );


    }


    /* Admin Panel Options.*/
    function admin_options() {
        ?>
        <h3><?php _e('SericaPay','SericaPay'); ?></h3>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table> <?php
    }

    /* Initialise Gateway Settings Form Fields. */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'SericaPay' ),
                'type' => 'checkbox',
                'label' => __( 'Enable SericaPay', 'SericaPay' ),
                'default' => 'no'
            ),

            'merchantId' => array(
                'title' => __( 'Merchant Id', 'SericaPay' ),
                'type' => 'text',
                'description' => __( 'Put merchant id here.', 'SericaPay' ),
                'desc_tip' => true,
                'default' => __( 'none', 'SericaPay' )
            ),
            'title' => array(
                'title' => __( 'Title', 'SericaPay' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'SericaPay' ),
                'desc_tip' => true,
                'default' => __( '<img src="https://sericatrading.com/media/SericaPayWoo.png" />', 'SericaPay' )
            ),
            'description' => array(
                'title' => __( 'Description', 'SericaPay' ),
                'type' => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'SericaPay' ),
                'default' => __( 'Special Offer! Get <strong>$10.00 off your next purchase</strong>. Choose <strong>SericaPay</strong> as your payment method, place order, and signup. (First time customers, only.)', 'SericaPay' )
            ),
        );

    }




    /* Process the payment and return the result. */
    function process_payment ($order_id) {
        global $woocommerce;

        $order = new WC_Order( $order_id );

        // Mark as on-hold
        $order->update_status('on-hold', __( 'Your order wont be shipped until the funds have cleared in our account.', 'woocommerce' ));

        // Reduce stock levels
        $order->reduce_order_stock();
        $items = $woocommerce->cart->get_cart();
      
        ?>
        <script type="text/javascript">
        window.onSericaPayLoad = function () {
            window.SericaPay.setup({
              'merchant': '<?php echo $this->merchantId ?>',
              'container': 'sericapay'
            });
            <?php
                foreach ($items as $i => $value) {
            ?>
                SericaPay.addProduct({
                    'id': '<?php echo $value['data']->get_title(); ?>',
                    'price': <?php echo $value['data']->get_price(); ?>,
                    'qty': <?php echo $value['quantity']; ?>
                });
            <?php
                }
            ?>
            SericaPay.setCartID('<?php echo $order_id; ?>');
            SericaPay.checkout();
        };
        </script>
        <script type="text/javascript" src="https://sericatrading.com/js/SericaPayButton.js"></script>
        <?php
        // Remove cart
        $woocommerce->cart->empty_cart();
        exit;

        // Return thankyou redirect
        // return array(
        //  'result'    => 'success',
        //  'redirect'  => add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(woocommerce_get_page_id('thanks'))))
        // );
    }


    /* Output for the order received page.   */
    function thankyou() {
        echo $this->instructions != '' ? wpautop( $this->instructions ) : '';
    }

}