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
/* Carrier deliver                                                                       */
/****************************************************************************************/

class cdi_c_Carrier_deliver {
	public static function init() {
	}

	/****************************************************************************************/
	/* Carrier References Livraisons                                                        */
	/****************************************************************************************/

	public static function cdi_carrier_update_settings() {
	}

	public static function cdi_isit_pickup_authorized() {
		return false;
	}

	public static function cdi_test_carrier() {
		return true;
	}

	public static function cdi_get_points_livraison( $relaytype ) {
	}

	public static function cdi_check_pickup_and_location() {
	}

	/****************************************************************************************/
	/* Carrier Front-end tracking                                                           */
	/****************************************************************************************/

	public static function cdi_text_preceding_trackingcode() {
		return null;
	}

	public static function cdi_url_trackingcode() {
		return null;
	}

	/****************************************************************************************/
	/* CDI Meta box in order panel                                                          */
	/****************************************************************************************/

	public static function cdi_metabox_initforcarrier( $order_id, $order ) {
	}

	public static function cdi_metabox_tracking_zone( $order_id, $order ) {
	}

	public static function cdi_metabox_parcel_settings( $order_id, $order ) {
	}

	public static function cdi_metabox_optional_choices( $order_id, $order ) {
	}

	public static function cdi_metabox_shipping_customer_choices( $order_id, $order ) {
	}

	public static function cdi_metabox_shipping_cn23( $order_id, $order ) {
	}

	public static function cdi_metabox_parcel_return( $order_id, $order ) {
	}

	public static function cdi_metabox_shipping_updatepickupaddress( $order_id, $order ) {
	}

	/****************************************************************************************/
	/* CDI Parcel returns                                                                   */
	/****************************************************************************************/

	public static function cdi_prodlabel_parcelreturn( $id_order, $productcode ) {
	}

	public static function cdi_isitopen_parcelreturn() {
		return false;
	}

	public static function cdi_isitvalidorder_parcelreturn() {
		return null;
	}

	public static function cdi_text_inviteprint_parcelreturn() {
		return null;
	}

	public static function cdi_url_carrier_following_parcelreturn() {
		return null;
	}

	public static function cdi_whichproducttouse_parcelreturn( $shippingcountry ) {
		return null;
	}

	public static function cdi_text_preceding_parcelreturn() {
		return null;
	}

	/****************************************************************************************/
	/* CDI fonctions                                                                        */
	/****************************************************************************************/

	public static function cdi_function_withoutsign_country( $country ) {
		return true;
	}

	public static function cdi_whereis_parcel( $order_id, $trackingcode ) {
		return '*** Pas de suivi pour le moment.';
	}

	public static function cdi_nochoicereturn_country( $country ) {
		return true;
	}

	/****************************************************************************************/
	/* CDI Bordereau de remise (dépôt)                                                              */
	/****************************************************************************************/

	public static function cdi_prod_remise_bordereau( $selected ) {
		$message = __( 'No delivery slip (deposit) for this carrier.', 'cdi' );
		return $message;
	}

	/****************************************************************************************/
	/* CDI Bordereaux Format                                                                */
	/****************************************************************************************/

	public static function cdi_prod_remise_format() {
		return null;
	}

}


