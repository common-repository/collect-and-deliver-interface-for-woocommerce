<?php
/*
 * Plugin Name: My Custom Carrier
 * Description: CDI - Model structure for a Custom carrier
 * Version: 1.0
 * Author: Halyra
 * Copyright: (c) 2020  Halyra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/****************************************************************************************/
/* Model Structure of a carrier "Custom" (notcdi)                                       */
/*											   */
/* You can have a look at the carriers already in CDI (exec.php files)		   */
/*											   */
/****************************************************************************************/

// In this model, your carrier is always internally named "notcdi", but you may define an external name in CDI settings (i.e. "Custom")

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$active_plugins = (array) get_option( 'active_plugins', array() );
if ( is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
}
if ( !in_array( 'woocommerce/woocommerce.php', $active_plugins ) or !in_array( 'collect-and-deliver-interface-for-woocommerce/cdi.php', $active_plugins ) ) {
	exit;
}

add_filter( 'cdi_notcdi_init', 'mycustomcarrier::init' ) ;
class mycustomcarrier {

	/***************************************************************************************/
	/* Init of your Custom Carrier		   			                  */
	/***************************************************************************************/
	public static function init( $return ) {
		/****************************************************************************************/
		/* Here code your special custom settings and special custom  init                      */
		/****************************************************************************************/
		// ..........
		// ..........
		// ..........	
		
		/****************************************************************************************/
		/* initial shipping label to product                                                    */
		/****************************************************************************************/		
		add_filter( 'cdi_notcdi_build_label_forparcel', __CLASS__ . '::mycustomcarrier_build_label_forparcel', 10, 4 );
		
		/****************************************************************************************/
		/* Carrier References Livraisons                                                        */
		/****************************************************************************************/	
		add_filter( 'cdi_notcdi_carrier_update_settings', __CLASS__ . '::mycustomcarrier_carrier_update_settings' );
		add_filter( 'cdi_notcdi_isit_pickup_authorized', __CLASS__ . '::mycustomcarrier_isit_pickup_authorized' );
		add_filter( 'cdi_notcdi_test_carrier', __CLASS__ . '::mycustomcarrier_test_carrier' );
		add_filter( 'cdi_notcdi_get_points_livraison', __CLASS__ . '::mycustomcarrier_get_points_livraison', 10, 2 );
		add_filter( 'cdi_notcdi_check_pickup_and_location', __CLASS__ . '::mycustomcarrier_check_pickup_and_location' );
		
		/****************************************************************************************/
		/* Carrier Front-end tracking                                                           */
		/****************************************************************************************/
		add_filter( 'cdi_notcdi_text_preceding_trackingcode', __CLASS__ . '::mycustomcarrier_text_preceding_trackingcode' );		
		add_filter( 'cdi_notcdi_url_trackingcode', __CLASS__ . '::mycustomcarrier_url_trackingcode' );
		
		/****************************************************************************************/
		/* CDI Meta box in order panel                                                          */
		/****************************************************************************************/
		add_filter( 'cdi_notcdi_metabox_initforcarrier', __CLASS__ . '::mycustomcarrier_metabox_initforcarrier', 10, 3 );
		add_filter( 'cdi_notcdi_metabox_tracking_zone', __CLASS__ . '::mycustomcarrier_metabox_tracking_zone', 10, 3 );
		add_filter( 'cdi_notcdi_metabox_parcel_settings', __CLASS__ . '::mycustomcarrier_metabox_parcel_settings', 10, 3 );
		add_filter( 'cdi_notcdi_metabox_optional_choices', __CLASS__ . '::mycustomcarrier_metabox_optional_choices', 10, 3 );
		add_filter( 'cdi_notcdi_metabox_shipping_customer_choices', __CLASS__ . '::mycustomcarrier_metabox_shipping_customer_choices', 10, 3 );
		add_filter( 'cdi_notcdi_metabox_shipping_cn23', __CLASS__ . '::mycustomcarrier_metabox_shipping_cn23', 10, 3 );
		add_filter( 'cdi_notcdi_metabox_parcel_return', __CLASS__ . '::mycustomcarrier_metabox_parcel_return', 10, 3 );
		add_filter( 'cdi_notcdi_metabox_shipping_updatepickupaddress', __CLASS__ . '::mycustomcarrier_metabox_shipping_updatepickupaddress', 10, 3 );
		
		/****************************************************************************************/
		/* CDI Parcel returns                                                                   */
		/****************************************************************************************/		
		add_filter( 'cdi_notcdi_prodlabel_parcelreturn', __CLASS__ . '::mycustomcarrier_prodlabel_parcelreturn', 10, 3 );
		add_filter( 'cdi_notcdi_isitopen_parcelreturn', __CLASS__ . '::mycustomcarrier_isitopen_parcelreturn' );		
		add_filter( 'cdi_notcdi_isitvalidorder_parcelreturn', __CLASS__ . '::mycustomcarrier_isitvalidorder_parcelreturn', 10, 2 );		
		add_filter( 'cdi_notcdi_text_inviteprint_parcelreturn', __CLASS__ . '::mycustomcarrier_text_inviteprint_parcelreturn' );		
		add_filter( 'cdi_notcdi_url_carrier_following_parcelreturn', __CLASS__ . '::mycustomcarrier_url_carrier_following_parcelreturn' );		
		add_filter( 'cdi_notcdi_whichproducttouse_parcelreturn', __CLASS__ . '::mycustomcarrier_whichproducttouse_parcelreturn', 10, 2 );		
		add_filter( 'cdi_notcdi_text_preceding_parcelreturn', __CLASS__ . '::mycustomcarrier_text_preceding_parcelreturn' );				

		/****************************************************************************************/
		/* CDI fonctions                                                                        */
		/****************************************************************************************/
		add_filter( 'cdi_notcdi_function_withoutsign_country', __CLASS__ . '::mycustomcarrier_function_withoutsign_country', 10, 2 );	
		add_filter( 'cdi_notcdi_whereis_parcel', __CLASS__ . '::mycustomcarrier_whereis_parcel', 10, 3 );	
		add_filter( 'cdi_notcdi_nochoicereturn_country', __CLASS__ . '::mycustomcarrier_nochoicereturn_country', 10, 2 );	

		/****************************************************************************************/
		/* CDI Bordereau de remise (dépôt)                                                      */
		/****************************************************************************************/
		add_filter( 'cdi_notcdi_prod_remise_bordereau', __CLASS__ . '::mycustomcarrier_prod_remise_bordereau', 10, 2 );
		
		/****************************************************************************************/
		/* CDI Bordereaux Format                                                                */
		/****************************************************************************************/
		add_filter( 'cdi_notcdi_prod_remise_format', __CLASS__ . '::mycustomcarrier_prod_remise_format' );
		
		return $return ;
	}

	/****************************************************************************************/
	/* initial shipping label to product                                                    */
	/****************************************************************************************/		
	public static function mycustomcarrier_build_label_forparcel($return = null , $cdi_nbrorderstodo, $cdi_rowcurrentorder, $array_for_carrier) {
		// ..........
		return $return ;
	}

	/****************************************************************************************/
	/* Carrier References Livraisons                                                        */
	/****************************************************************************************/

	public static function mycustomcarrier_carrier_update_settings($return = null) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_isit_pickup_authorized($return = false) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_test_carrier($return = true) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_get_points_livraison(  $return = null, $relaytype ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_check_pickup_and_location($return = true) {
		// ..........
		return $return ;
	}

	/****************************************************************************************/
	/* Carrier Front-end tracking                                                           */
	/****************************************************************************************/

	public static function mycustomcarrier_text_preceding_trackingcode($return = null) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_url_trackingcode($return) {
		// ..........
		return $return ;
	}

	/****************************************************************************************/
	/* CDI Meta box in order panel                                                          */
	/****************************************************************************************/

	public static function mycustomcarrier_metabox_initforcarrier( $return = true, $order_id, $order ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_metabox_tracking_zone( $return = null, $order_id, $order ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_metabox_parcel_settings( $return = null, $order_id, $order ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_metabox_optional_choices( $return = null, $order_id, $order ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_metabox_shipping_customer_choices( $return = null, $order_id, $order ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_metabox_shipping_cn23( $return = null, $order_id, $order ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_metabox_parcel_return( $return = null, $order_id, $order ) {
		// ..........
		return $return ;
	}

	public static function cdi_metabox_shipping_updatepickupaddress( $return = null, $order_id, $order ) {
		// ..........
		return $return ;
	}

	/****************************************************************************************/
	/* CDI Parcel returns                                                                   */
	/****************************************************************************************/

	public static function mycustomcarrier_prodlabel_parcelreturn( $return = null, $id_order, $productcode ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_isitopen_parcelreturn($return = false) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_isitvalidorder_parcelreturn( $return = false, $id_order ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_text_inviteprint_parcelreturn($return = null) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_url_carrier_following_parcelreturn($return = null) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_whichproducttouse_parcelreturn( $return = null, $shippingcountry ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_text_preceding_parcelreturn($return = null) {
		// ..........
		return $return ;
	}

	/****************************************************************************************/
	/* CDI fonctions                                                                        */
	/****************************************************************************************/

	public static function mycustomcarrier_function_withoutsign_country( $return = true, $country ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_whereis_parcel( $return = '*** Pas de suivi pour le moment.', $order_id, $trackingcode ) {
		// ..........
		return $return ;
	}

	public static function mycustomcarrier_nochoicereturn_country( $return = true, $country ) {
		// ..........
		return $return ;
	}

	/****************************************************************************************/
	/* CDI Bordereau de remise (dépôt)                                                      */
	/****************************************************************************************/

	public static function mycustomcarrier_prod_remise_bordereau( $return, $selected ) {
		$return = __( 'No delivery slip (deposit) for this carrier.', 'cdi' );
		// ..........
		return $return ;	
	}

	/****************************************************************************************/
	/* CDI Bordereaux Format                                                                */
	/****************************************************************************************/

	public static function mycustomcarrier_prod_remise_format( $return = null) {
		// ..........
		return $return ;
	}

}
