<?php

/**
 * This file is part of the CDI - Collect and Deliver Interface plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/****************************************************************************************/
/* CDI Meta box in Subscription panel                                                          */
/****************************************************************************************/
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;
class cdi_c_Metabox_subscription {

	public static function init() {
		add_action( 'add_meta_boxes', __CLASS__ . '::cdi_addmetabox_subscription' );
		add_action( 'woocommerce_process_shop_order_meta', __CLASS__ . '::cdi_save_metabox_subscription', 99 );
	}
	
	public static function cdi_addmetabox_subscription() {
		$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop_subscription' )
		: 'shop_subscription';		
		if ( 'shop_subscription' == $screen ) {
			  add_meta_box( 'cdi-metabox-display-subscription', 'CDI Subscription', __CLASS__ . '::cdi_create_box_content_subscription', $screen, 'side', 'low' );
		}
	}

	public static function cdi_create_box_content_subscription($post_or_order_object) {
		global $woocommerce, $post ;		
		wp_nonce_field( 'cdi_save_metabox_subscription', 'cdi_save_metabox_subscription_nonce' );
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object ;
		$order_id = $order->get_id();
		$ordertype = $order->get_type() ;
                if ('shop_subscription' !== $ordertype) {
                	return ;
                }
		$carrier   = $order->get_meta( '_cdi_meta_carrier' );
		if ( ! $carrier ) {
			// Manage compatibility for orders passed and processed previously in version 3.7.x ($carrier)
			$shippingmethod = $order->get_meta( '_cdi_refshippingmethod' );
			if ( $shippingmethod and strpos( 'x' . $shippingmethod, 'colissimo_shippingzone_method_' ) > 0 ) {
				$carrier = 'colissimo'; // Old order which has been producted under CDI shipping method - May be or not already entered in CDI process
			} else {
				$carrier = 'colissimo'; // Old order not producted under CDI shipping method (for instance: Flat rates )
				// Allow change of carrier for this repatriation order with a filter
				$carrier = apply_filters( 'cdi_filterstring_subscription_repatriation_change_carrier', $carrier, $post, $order->get_meta( '_cdi_refshippingmethod' ) );
			}
			$order->update_meta_data('_cdi_meta_carrier', $carrier );
			$order->save();
		}
					
		$shippingmethod = $order->get_meta( '_cdi_refshippingmethod' );
		$method_name = $order->get_meta( '_cdi_meta_shippingmethod_name' ); 
		?>
		<p style="margin-bottom:2px;"><a><?php _e( 'Original shipping : ', 'cdi' ); ?></a><a style="color:black;"><?php echo esc_attr( $method_name ); ?></a> : <?php echo esc_attr( $shippingmethod ); ?></p>
		<p> </p> 

		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Future shippings :', 'cdi' ); ?></div><p style="clear:both"></p>               
		<p style='width:35%; float:left;  margin-top:5px;'><a><?php _e( 'Carrier : ', 'cdi' ); ?>
			<?php
			woocommerce_wp_select(
				array(
					'name' => '_cdi_meta_carrier',
					'type' => 'text',
					'options' => array(
						'colissimo' => __( 'Colissimo', 'cdi' ),
						'mondialrelay' => __( 'Mondial Relay', 'cdi' ),
						'ups'      => __( 'UPS', 'cdi' ),
						'collect'      => __( 'Collect', 'cdi' ),
						'notcdi' => cdi_c_Function::cdi_get_libelle_carrier( 'notcdi' ),
					),
					'style' => 'width:60%; float:left;',
					'id'   => '_cdi_meta_carrier',
					'label' => '',
				), $order,
			);
			?>
			 </a></p><p style="clear:both"></p>

		<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Forced product code : ', 'cdi' ); ?>
																	   <?php
			woocommerce_wp_text_input(
				array(
					'name' => '_cdi_meta_productCode',
					'type' => 'text',
					'style' => 'width:45%; float:left;',
					'id'   => '_cdi_meta_productCode',
					'label' => '',
				), $order,
			);
			?>
			 </a></p><p style="clear:both"></p>
			
		<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Pickup location id : ', 'cdi' ); ?>
																	   <?php
			woocommerce_wp_text_input(
				array(
					'name' => '_cdi_meta_pickupLocationId',
					'type' => 'text',
					'style' => 'width:45%; float:left;',
					'id'   => '_cdi_meta_pickupLocationId',
					'label' => '',
				), $order,
			);
			?>
			 </a></p><p style="clear:both"></p>
			 
		<p style="margin-bottom:2px;"><a><?php _e( "Location : ", "cdi" ); ?></a><a style="color:black;"><?php echo esc_attr( $order->get_meta( "_cdi_meta_pickupLocationlabel" ) ); ?></a> </p>			 

		<?php
	}

	public static function cdi_save_metabox_subscription( $post_id ) {
		global $post, $post_type;
		$order = wc_get_order( $post_id );	
		if ( ! $order ) {
			return $post_id;
		}
		$order_id = $order->get_id();		
		$ordertype = $order->get_type() ;		
                if ('shop_subscription' !== $ordertype) {
                	return ;
                }
		if ( ! isset( $_POST['cdi_save_metabox_subscription_nonce'] ) ) {
			return ; }
		if ( ! wp_verify_nonce( $_POST['cdi_save_metabox_subscription_nonce'], 'cdi_save_metabox_subscription' ) ) {
			return ; }
		
		if ( ( $_POST['_cdi_meta_carrier'] !== $order->get_meta( '_cdi_meta_carrier' ) ) or
		  ( isset( $_POST['_cdi_meta_productCode'] ) and ( $_POST['_cdi_meta_productCode'] !== $order->get_meta( '_cdi_meta_productCode' ) ) ) or
		  ( $_POST['_cdi_meta_pickupLocationId'] !== $order->get_meta( '_cdi_meta_pickupLocationId' ) ) ) {
			$order->update_meta_data( '_cdi_meta_carrierredirected', 'yes' );
		}

		if ( isset( $_POST['_cdi_meta_carrier'] ) ) {
			$order->update_meta_data( '_cdi_meta_carrier', sanitize_text_field( $_POST['_cdi_meta_carrier'] ) ); }
		if ( isset( $_POST['_cdi_meta_productCode'] ) ) {
			$order->update_meta_data( '_cdi_meta_productCode', sanitize_text_field( $_POST['_cdi_meta_productCode'] ) ); }
		if ( isset( $_POST['_cdi_meta_pickupLocationId'] ) ) {
			$new_pickupLocationId = sanitize_text_field( $_POST['_cdi_meta_pickupLocationId'] );		
			$last_pickupLocationId = $order->get_meta( '_cdi_meta_lastpickupLocationId' );
			$order->update_meta_data( '_cdi_meta_pickupLocationId', $new_pickupLocationId ); 
			if ( (! $new_pickupLocationId ) or ( $new_pickupLocationId == null ) or ( $new_pickupLocationId == '' ) ) {
				$order->update_meta_data( '_cdi_meta_lastpickupLocationId', '' );				
				$order->update_meta_data( '_cdi_meta_pickupLocationlabel', '' );
				$order->update_meta_data( '_cdi_meta_pickupfulladdress', '' );
			}else{			
				if ( $new_pickupLocationId != $last_pickupLocationId ) {// try to get new address	
					$carrier = $order->get_meta( '_cdi_meta_carrier' );
					$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
					$route = 'cdi_c_Carrier_' . $carrier . '::cdi_metabox_shipping_updatepickupaddress';
					( $route )( $order_id, $order );
				}
			}
		}	
		$order->save();				
	}

}


?>
