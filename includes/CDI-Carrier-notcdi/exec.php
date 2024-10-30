<?php
/*
 * Plugin Name: CDI - Collect and Deliver Interface
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/****************************************************************************************/
/* Carrier notcdi (Custom)                                                              */
/****************************************************************************************/

class cdi_c_Carrier_notcdi {
	public static function init() {
		return apply_filters( 'cdi_notcdi_init', $return = null ) ;
	}

	/****************************************************************************************/
	/* Carrier References Livraisons                                                        */
	/****************************************************************************************/

	public static function cdi_carrier_update_settings() {
		return apply_filters( 'cdi_notcdi_carrier_update_settings', $return = null ) ;
	}

	public static function cdi_isit_pickup_authorized() {
		return apply_filters( 'cdi_notcdi_isit_pickup_authorized', $return = false ) ;
	}

	public static function cdi_test_carrier() {
		return apply_filters( 'cdi_notcdi_test_carrier', $return = true ) ;
	}

	public static function cdi_get_points_livraison( $relaytype ) {
		return apply_filters( 'cdi_notcdi_get_points_livraison', $return = null, $relaytype ) ;	
	}

	public static function cdi_check_pickup_and_location() {
		return apply_filters( 'cdi_notcdi_check_pickup_and_location', $return = true ) ;
	}

	/****************************************************************************************/
	/* Carrier Front-end tracking                                                           */
	/****************************************************************************************/

	public static function cdi_text_preceding_trackingcode() {
		return apply_filters( 'cdi_notcdi_text_preceding_trackingcode', $return = null ) ;
	}

	public static function cdi_url_trackingcode() {
		return apply_filters( 'cdi_notcdi_url_trackingcode', $return = null ) ;
	}

	/****************************************************************************************/
	/* CDI Meta box in order panel                                                          */
	/****************************************************************************************/

	public static function cdi_metabox_initforcarrier( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_initforcarrier', $return = true, $order_id, $order ) ;
	}

	public static function cdi_metabox_tracking_zone( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_tracking_zone', $return = null, $order_id, $order ) ;
	}

	public static function cdi_metabox_parcel_settings( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_parcel_settings', $return = null, $order_id, $order ) ;
	}

	public static function cdi_metabox_optional_choices( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_optional_choices', $return = null, $order_id, $order ) ;
	}

	public static function cdi_metabox_shipping_customer_choices( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_shipping_customer_choices', $return = null, $order_id, $order ) ;
	}

	public static function cdi_metabox_shipping_cn23( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_shipping_cn23', $return = null, $order_id, $order ) ;
	}

	public static function cdi_metabox_parcel_return( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_parcel_return', $return = null, $order_id, $order ) ;
	}

	public static function cdi_metabox_shipping_updatepickupaddress( $order_id, $order ) {
		return apply_filters( 'cdi_notcdi_metabox_shipping_updatepickupaddress', $return = null, $order_id, $order ) ;
	}

	/****************************************************************************************/
	/* CDI Parcel returns                                                                   */
	/****************************************************************************************/

	public static function cdi_prodlabel_parcelreturn( $id_order, $productcode ) {
		return apply_filters( 'cdi_notcdi_prodlabel_parcelreturn', $return = null, $id_order, $productcode ) ;
	}

	public static function cdi_isitopen_parcelreturn() {
		return apply_filters( 'cdi_notcdi_isitopen_parcelreturn', $return = false ) ;
	}

	public static function cdi_isitvalidorder_parcelreturn($id_order) {
		return apply_filters( 'cdi_notcdi_isitvalidorder_parcelreturn', $return = false, $id_order ) ;
	}

	public static function cdi_text_inviteprint_parcelreturn() {
		return apply_filters( 'cdi_notcdi_text_inviteprint_parcelreturn', $return = null ) ;
	}

	public static function cdi_url_carrier_following_parcelreturn() {
		return apply_filters( 'cdi_notcdi_url_carrier_following_parcelreturn', $return = null ) ;
	}

	public static function cdi_whichproducttouse_parcelreturn( $shippingcountry ) {
		return apply_filters( 'cdi_notcdi_whichproducttouse_parcelreturn', $return = null, $shippingcountry ) ;
	}

	public static function cdi_text_preceding_parcelreturn() {
		return apply_filters( 'cdi_notcdi_text_preceding_parcelreturn', $return = null ) ;
	}

	/****************************************************************************************/
	/* CDI fonctions                                                                        */
	/****************************************************************************************/

	public static function cdi_function_withoutsign_country( $country ) {
		return apply_filters( 'cdi_notcdi_function_withoutsign_country', $return = true, $country ) ;
	}

	public static function cdi_whereis_parcel( $order_id, $trackingcode ) {
		return apply_filters( 'cdi_notcdi_whereis_parcel', $return = '*** Pas de suivi pour le moment.', $order_id, $trackingcode ) ;
	}

	public static function cdi_nochoicereturn_country( $country ) {
		return apply_filters( 'cdi_notcdi_nochoicereturn_country', $return = true, $country ) ;
	}

	/****************************************************************************************/
	/* CDI Bordereau de remise (dépôt)                                                              */
	/****************************************************************************************/

	public static function cdi_prod_remise_bordereau( $selected ) {
		$return = __( 'No delivery slip (deposit) for this carrier.', 'cdi' );
		return apply_filters( 'cdi_notcdi_prod_remise_bordereau', $return, $selected ) ;	
	}

	/****************************************************************************************/
	/* CDI Bordereaux Format                                                                */
	/****************************************************************************************/

	public static function cdi_prod_remise_format() {
		return apply_filters( 'cdi_notcdi_prod_remise_format', $return = null ) ;
	}

}


