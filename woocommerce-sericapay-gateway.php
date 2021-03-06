<?php

/**
 * @wordpress-plugin
 * Plugin Name:       SericaPay for WooCommerce
 * Plugin URI:        http://woothemes.com/products/woocommerce-sericapay/
 * Description:       Easily enable payments with SericaPay
 * Version:           1.0.0
 * Author:            WooThemes
 * Author URI:        http://woothemes.com/
 * Developer:         Serica
 * Developer URI:     http://sericapay.com/
 *
 * Copyright:         © 2009-2015 WooThemes.
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 *
 * GitHub Plugin URI: https://github.com/digitaltangibletrust/woocommerce-sericapay
 */

/**
 * Exit if accessed directly.
 */
if (!defined('ABSPATH')) {
    exit();
}

/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'SericaPay_register_plugin_links', 10, 2 );
function SericaPay_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="https://sericapay.com/" target="_blank">' . __( 'Serica Pay', 'sp' ) . '</a>';
	}
	return $links;
}


/* WooCommerce fallback notice */
function woocommerce_sericapay_fallback_notice() {
    echo '<div class="error"><p>' . sprintf( __( 'SericaPay Payment Gateway depends on the last version of %s to work!', 'SericaPay' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
}


/* Load functions */
function custom_payment_gateway_load() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'woocommerce_sericapay_fallback_notice' );
        return;
    }
   
    function wc_add_sericapay_gw( $methods ) {
        $methods[] = 'WC_SericaPay';
        return $methods;
    }
	add_filter( 'woocommerce_payment_gateways', 'wc_add_sericapay_gw' );
	
    // Include the WooCommerce Custom Payment Gateways classes.
    require_once plugin_dir_path( __FILE__ ) . 'SericaPay.php';
}
add_action( 'plugins_loaded', 'custom_payment_gateway_load', 0 );


/* Adds custom settings url in plugins page */
function sericapay_action_links( $links ) {
    $settings = array(
		'settings' => sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_sericapay' ),
		__( 'SericaPay', 'sericapay' )
		)
    );

    return array_merge( $settings, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'sericapay_action_links' );

?>