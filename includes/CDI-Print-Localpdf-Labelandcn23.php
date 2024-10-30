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
/* print pdf file when requested from admin                                             */
/****************************************************************************************/

/****************************************************************************************/
// For storage of label_pdf and cn23_pdf (used on each order and accesible by admin) the choice was made to store them in an upload/cdistore file
// and not in meta_post to avoid clutter of the data base.
// For return_pdf (more occasional and used by admin and frontend) storage is in meta_post.                                       */
/****************************************************************************************/

class cdi_c_Print_Localpdf_Labelandcn23 {
	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_local_label_pdf' );
		add_action( 'admin_init', __CLASS__ . '::cdi_local_cn23_pdf' );
		add_action( 'admin_init', __CLASS__ . '::cdi_admin_createreturnlabel_pdf' );
		add_action( 'admin_init', __CLASS__ . '::cdi_admin_return_pdf' );
		add_action( 'admin_init', __CLASS__ . '::cdi_admin_createreturncn23_pdf' );
		add_action( 'admin_init', __CLASS__ . '::cdi_admin_returncn23_pdf' );
	}
	public static function cdi_local_label_pdf() {
		if ( isset( $_POST['cdi_local_label_pdf'] ) && isset( $_POST['cdi_local_label_pdf_nonce'] ) && wp_verify_nonce( $_POST['cdi_local_label_pdf_nonce'], 'cdi_local_label_pdf' ) ) {
			global $woocommerce;
			global $wpdb;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$order_id = sanitize_text_field( $_POST['order_id'] );
				$cdi_loclabel = cdi_c_Function::cdi_uploads_get_contents( $order_id, 'label' );
				if ( $cdi_loclabel ) {
					$cdi_loclabel_pdf = base64_decode( $cdi_loclabel );
					$out = fopen( 'php://output', 'w' );
					$thepdffile = 'Label-' . $order_id . '-' . date( 'YmdHis' ) . '.pdf';
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: attachment; filename=' . $thepdffile );
					fwrite( $out, $cdi_loclabel_pdf );
					fclose( $out );
					die();
				}
			} // End current_user_can
		} // End $_POST['cdi_local_label_pdf'
	} // End function cdi_local_label_pdf

	public static function cdi_local_cn23_pdf() {
		if ( isset( $_POST['cdi_local_cn23_pdf'] ) && isset( $_POST['cdi_local_cn23_pdf_nonce'] ) && wp_verify_nonce( $_POST['cdi_local_cn23_pdf_nonce'], 'cdi_local_cn23_pdf' ) ) {
			global $woocommerce;
			global $wpdb;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$order_id = sanitize_text_field( $_POST['order_id'] );
				$cdi_loccn23 = cdi_c_Function::cdi_uploads_get_contents( $order_id, 'cn23' );
				if ( $cdi_loccn23 ) {
					$cdi_loccn23_pdf = base64_decode( $cdi_loccn23 );
					$out = fopen( 'php://output', 'w' );
					$thepdffile = 'cn23-' . $order_id . '-' . date( 'YmdHis' ) . '.pdf';
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: attachment; filename=' . $thepdffile );
					fwrite( $out, $cdi_loccn23_pdf );
					fclose( $out );
					die();
				}
			} // End current_user_can
		} // End $_POST['cdi_local_cn23_pdf'
	} // End function cdi_local_cn23_pdf

	public static function cdi_admin_createreturnlabel_pdf() {
		if ( isset( $_POST['cdi_admin_createreturnlabel_pdf'] ) && isset( $_POST['cdi_admin_createreturnlabel_pdf_nonce'] ) && wp_verify_nonce( $_POST['cdi_admin_createreturnlabel_pdf_nonce'], 'cdi_admin_createreturnlabel_pdf' ) ) {
			global $woocommerce;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$order_id = sanitize_text_field( $_POST['order_id'] );
				$order = wc_get_order( $order_id );
				$carrier = $order->get_meta( '_cdi_meta_carrier' );
				$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
				$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );
				$shippingcountry = $array_for_carrier['shipping_country'];
				// Test if Product code exist in tables
				$route = 'cdi_c_Carrier_' . $carrier . '::cdi_whichproducttouse_parcelreturn';
				$productcode = ( $route )( $shippingcountry );
				if ( $productcode && $productcode !== '' ) {
					$route = 'cdi_c_Carrier_' . $carrier . '::cdi_prodlabel_parcelreturn';
					( $route )( $order_id, $productcode );
					cdi_c_Function::cdi_stat( 'RET-aff' );
				}
			} // End current_user_can
		} // End $_POST['cdi_admin_createreturnlabel_pdf'
	} // End function cdi_admin_createreturnlabel_pdf

	public static function cdi_admin_return_pdf() {
		if ( isset( $_POST['cdi_admin_return_pdf'] ) && isset( $_POST['cdi_admin_return_pdf_nonce'] ) && wp_verify_nonce( $_POST['cdi_admin_return_pdf_nonce'], 'cdi_admin_return_pdf' ) ) {
			global $woocommerce;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$order_id = sanitize_text_field( $_POST['order_id'] );
				$order = wc_get_order( $order_id );
				$cdi_returnlabel = $order->get_meta( '_cdi_meta_base64_return' );
				if ( $cdi_returnlabel ) {
					$cdi_returnlabel_pdf = base64_decode( $cdi_returnlabel );
					$out = fopen( 'php://output', 'w' );
					$thepdffile = 'Return-' . $order_id . '-' . date( 'YmdHis' ) . '.pdf';
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: attachment; filename=' . $thepdffile );
					fwrite( $out, $cdi_returnlabel_pdf );
					fclose( $out );
					die();
				}
			} // End current_user_can
		} // End $_POST['cdi_admin_return_pdf'
	} // End function cdi_admin_return_pdf

	public static function cdi_admin_createreturncn23_pdf() {
		if ( isset( $_POST['cdi_admin_createreturncn23_pdf'] ) && isset( $_POST['cdi_admin_createreturncn23_pdf_nonce'] ) && wp_verify_nonce( $_POST['cdi_admin_createreturncn23_pdf_nonce'], 'cdi_admin_createreturncn23_pdf' ) ) {
			global $woocommerce;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$order_id = sanitize_text_field( $_POST['order_id'] );
				$order = wc_get_order( $order_id );
				$tracking = $order->get_meta( '_cdi_meta_parcelnumber_return' );
				$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );
				$base64cn23 = cdi_c_Pdf_Workshop::cdi_build_cn23_pdf( $order_id, $tracking, $array_for_carrier, $r = 'yes' );
				if ( $base64cn23 ) {
					$order->update_meta_data( '_cdi_meta_base64_returncn23', $base64cn23 );
					$order->save();
				}
			} // End current_user_can
		} // End $_POST['cdi_admin_createreturncn23_pdf'
	} // End function cdi_admin_createreturncn23_pdf

	public static function cdi_admin_returncn23_pdf() {
		if ( isset( $_POST['cdi_admin_returncn23_pdf'] ) && isset( $_POST['cdi_admin_returncn23_pdf_nonce'] ) && wp_verify_nonce( $_POST['cdi_admin_returncn23_pdf_nonce'], 'cdi_admin_returncn23_pdf' ) ) {
			global $woocommerce;
			global $wpdb;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$order_id = sanitize_text_field( $_POST['order_id'] );
				$order = wc_get_order( $order_id );
				$cdi_returncn23 = $order->get_meta( '_cdi_meta_base64_returncn23' );
				if ( $cdi_returncn23 ) {
					$cdi_returncn23_pdf = base64_decode( $cdi_returncn23 );
					$out = fopen( 'php://output', 'w' );
					$thepdffile = 'Return-' . $order_id . '-' . date( 'YmdHis' ) . '.pdf';
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: attachment; filename=' . $thepdffile );
					fwrite( $out, $cdi_returncn23_pdf );
					fclose( $out );
					die();
				}
			} // End current_user_can
		} // End $_POST['cdi_admin_returncn23_pdf'
	} // End function cdi_admin_returncn23_pdf

} // End class

