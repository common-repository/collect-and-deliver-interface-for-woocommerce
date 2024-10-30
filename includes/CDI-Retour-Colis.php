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
/* parcel returns                                                                       */
/****************************************************************************************/

class cdi_c_Retour_Colis {
	public static function init() {
		add_action( 'woocommerce_view_order', __CLASS__ . '::cdi_display_retourcolis' );
		add_action( 'init', __CLASS__ . '::cdi_print_returnlabel_pdf' );
	}

	public static function cdi_print_returnlabel_pdf() {
		if ( isset( $_POST['cdi_print_returnlabel_pdf'] ) && isset( $_POST['cdi_print_returnlabel_pdf_nonce'] ) && wp_verify_nonce( $_POST['cdi_print_returnlabel_pdf_nonce'], 'cdi_print_returnlabel_pdf' ) ) {
			global $woocommerce;
			$id_order = sanitize_text_field( $_POST['idreturnlabel'] );
			$order = wc_get_order( $id_order );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $id_order, 'msg' );
			$base64return = $order->get_meta( '_cdi_meta_base64_return' );
			if ( $base64return ) {
				$cdi_loclabel_pdf = base64_decode( $base64return );
				$out = fopen( 'php://output', 'w' );
				$thepdffile = 'Return-' . $id_order . '-' . date( 'YmdHis' ) . '.pdf';
				header( 'Content-Type: application/pdf' );
				header( 'Content-Disposition: attachment; filename=' . $thepdffile );
				fwrite( $out, $cdi_loclabel_pdf );
				fclose( $out );
				die();
			}
		} // End $_POST['cdi_print_returnlabel_pdf'
	} // End function cdi_print_returnlabel_pdf


	public static function cdi_display_retourcolis( $id_order ) {
		global $woocommerce;
		$order = wc_get_order( $id_order );
		// If posted, get and store the return label
		if ( isset( $_POST['cdi_getparcelreturn'] ) ) {
			$productcode = sanitize_text_field( $_POST['productcode'] );
			$carrier = $order->get_meta( '_cdi_meta_carrier' );
			$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
			$route = 'cdi_c_Carrier_' . $carrier . '::cdi_prodlabel_parcelreturn';
			( $route )( $id_order, $productcode );
			cdi_c_Function::cdi_stat( 'RET-aff' );
		}

		// Normal processing of order view
		$statusparcelreturn = 'no';
		$carrier = $order->get_meta( '_cdi_meta_carrier' );
		$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_isitopen_parcelreturn';
		$statusparcelreturn = ( $route )();
		if ( $statusparcelreturn == 'yes' ) {
			$order = new WC_Order( $id_order );
			// $statusorder = $order->post->post_status ;  // Deprecated WC3
			$statusorder = $order->get_status();
			if ( $order->get_meta( '_cdi_meta_status' ) == 'intruck' ) {
				$retoureligible = apply_filters( 'cdi_filterstring_retourcolis_eligible', 'yes', $order );
			} else {
				$retoureligible = 'no';
			}
			$retoureligible = apply_filters( 'cdi_filterstring_retourcolis_eligible_force', $retoureligible, $order);
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $id_order . ' - ' . $statusorder . ' - ' . $retoureligible, 'msg' );
			if ( $retoureligible == 'yes' ) {
				$route = 'cdi_c_Carrier_' . $carrier . '::cdi_isitvalidorder_parcelreturn';
				$trackingheaders_parcelreturn = ( $route )( $id_order );
				if ( $trackingheaders_parcelreturn ) {
					$cdi_meta_exist_uploads_label = $order->get_meta( '_cdi_meta_exist_uploads_label' );
					if ( $cdi_meta_exist_uploads_label == true ) {
						// Here we can process the parcel return function
						// $completeddate = $order->post->post_date ; // Deprecated WC3
						$completeddate = $order->get_date_created();
						$nbdaytoreturn = $order->get_meta( '_cdi_meta_nbdayparcelreturn' );
						$daynoreturn = ( $nbdaytoreturn * 60 * 60 * 24 ) + strtotime( $completeddate );
						$today = strtotime( 'now' );
						if ( $today < $daynoreturn ) {
							$base64return = $order->get_meta( '_cdi_meta_base64_return' );
							if ( $base64return ) {
								// Display the existing parcel return label
								$route = 'cdi_c_Carrier_' . $carrier . '::cdi_text_inviteprint_parcelreturn';
								$txt = ( $route )();
								$val = __( 'Print your parcel return label', 'cdi' );
								$route = 'cdi_c_Carrier_' . $carrier . '::cdi_url_carrier_following_parcelreturn';
								$url = ( $route )();
								echo '<div id="divcdiprintparcelreturn"><form method="post" id="cdi_print_returnlabel_pdf" action="">' . '<input type="hidden" name="idreturnlabel" value="' . esc_attr( $id_order ) . '" />' . ' <input type="submit" name="cdi_print_returnlabel_pdf" value="' . esc_attr( $val ) . '"  title="Print your parcel return label" /> <p> ' . esc_attr( $txt ) . '</p>';
								echo '<a href="' . esc_url( $url ) . '" onclick="window.open(this.href); return false;" > ' . esc_url( $url ) . ' </a>';
								wp_nonce_field( 'cdi_print_returnlabel_pdf', 'cdi_print_returnlabel_pdf_nonce' );
								echo '</form></div>';
							} else {
								// Create the parcel return label and display it
								$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $id_order );
								$shippingcountry = $array_for_carrier['shipping_country'];
								// Test if Product code exist in tables
								$route = 'cdi_c_Carrier_' . $carrier . '::cdi_whichproducttouse_parcelreturn';
								$productcode = ( $route )( $shippingcountry );
								if ( $productcode && $productcode !== '' ) {
									$route = 'cdi_c_Carrier_' . $carrier . '::cdi_text_preceding_parcelreturn';
									;
									$txt = ( $route )();
									$val = __( 'Request for a parcel return label', 'cdi' );
									echo '<div id="divcdigetparcelreturn"><form method="post" id="cdi_getparcelreturn" action="">' . esc_attr( $txt ) . ' <input type="submit" name="cdi_getparcelreturn" value="' . esc_attr( $val ) . '"  title="Request for a parcel return label"/>' . '<input type="hidden" name="productcode" value="' . esc_attr( $productcode ) . '"/>';
									// wp_nonce_field( 'cdi_getparcelreturn_run', 'cdi_getparcelreturn_run_nonce');
									echo '</form></div>   ';
								}
							}
						}
					}
				}
			}
		}
	}

	public static function cdi_check_returnlabel_eligible( $id_order ) {
		global $woocommerce;
		$order = wc_get_order( $id_order );
		$return = false;
		$statusparcelreturn = 'no';
		$carrier = $order->get_meta( '_cdi_meta_carrier' );
		$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_isitopen_parcelreturn';
		$statusparcelreturn = ( $route )();
		if ( $statusparcelreturn == 'yes' ) {
			$order = new WC_Order( $id_order );
			$statusorder = $order->get_status();
			if ( $order->get_meta( '_cdi_meta_status' ) == 'intruck' ) {
				$retoureligible = apply_filters( 'cdi_filterstring_retourcolis_eligible', 'yes', $order );
			} else {
				$retoureligible = 'no';
			}
			$retoureligible = apply_filters( 'cdi_filterstring_retourcolis_eligible_force', $retoureligible, $order);
			if ( $retoureligible == 'yes' ) {
				$route = 'cdi_c_Carrier_' . $carrier . '::cdi_isitvalidorder_parcelreturn';
				$trackingheaders_parcelreturn = ( $route )( $id_order );
				if ( $trackingheaders_parcelreturn ) {
					$cdi_meta_exist_uploads_label = $order->get_meta( '_cdi_meta_exist_uploads_label' );
					if ( $cdi_meta_exist_uploads_label == true ) {
						$completeddate = $order->get_date_created();
						$nbdaytoreturn = $order->get_meta( '_cdi_meta_nbdayparcelreturn' );
						if (is_numeric($nbdaytoreturn) ) {
							$daynoreturn = ( $nbdaytoreturn * 60 * 60 * 24 ) + strtotime( $completeddate );
							$today = strtotime( 'now' );
							if ( $today < $daynoreturn ) {
								$base64return = $order->get_meta( '_cdi_meta_base64_return' );
								if ( ! $base64return ) {
									$return = true;
								}
							}
						}
					}
				}
			}
		}
		return $return;
	}

}




