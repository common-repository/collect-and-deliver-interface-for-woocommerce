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
/* Gateway Custom                                                                       */
/****************************************************************************************/
class cdi_c_Gateway_Custom {
	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_custom_run' );
	}
	public static function cdi_custom_run() {
		if ( isset( $_POST['cdi_gateway_custom'] ) && isset( $_POST['cdi_custom_run_nonce'] ) && wp_verify_nonce( $_POST['cdi_custom_run_nonce'], 'cdi_custom_run' ) ) {
			global $woocommerce;
			global $wpdb;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cdi' );
				if ( count( $results ) ) {
					$errorws = null ;
					$cdi_nbrorderstodo = 0;
					$cdi_rowcurrentorder = 0;
					$cdi_nbrtrkcode = 0;
					foreach ( $results as $row ) {
						$cdi_tracking = $row->cdi_tracking;
						$order = wc_get_order( $row->cdi_order_id );
						$carrier = $order->get_meta( '_cdi_meta_carrier' );
						if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'notcdi' ) ) {
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
						}
					}
					if ( $cdi_nbrorderstodo > 0 ) {
						foreach ( $results as $row ) {
							$errorws = null ;				
							$cdi_tracking = $row->cdi_tracking;
							$order = wc_get_order( $row->cdi_order_id );
							$carrier = $order->get_meta( '_cdi_meta_carrier' );
							if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'notcdi' ) ) {
								$cdi_rowcurrentorder = $cdi_rowcurrentorder + 1;
								$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $row );
								cdi_c_Function::cdi_debug( __LINE__, __FILE__, $array_for_carrier['order_id'], 'msg' );
								$cdi_tracking = apply_filters( 'cdi_notcdi_build_label_forparcel', $cdi_tracking = false, $cdi_nbrorderstodo, $cdi_rowcurrentorder, $array_for_carrier );
								// This second filter is only for legacy users of CDI wo can use it. It is now deprecated.
								$cdi_tracking = apply_filters( 'cdi_custom_gateway_exec', $cdi_tracking, $cdi_nbrorderstodo, $cdi_rowcurrentorder, $array_for_carrier );
								cdi_c_Function::cdi_debug( __LINE__, __FILE__, $cdi_tracking, 'msg' );
								//
								// When the return of the filter is a string, it is a simplified return that only gives the tracking code.
								//
								if ( is_string( $cdi_tracking ) ) {
									$cdi_tracking = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $cdi_tracking ) );
									$cdi_nbrtrkcode = $cdi_nbrtrkcode + 1;
									cdi_c_Function::cdi_stat( 'CUS-aff' );
									$order_id  = $row->cdi_order_id;
									$x = $wpdb->update( $wpdb->prefix . 'cdi', array( 'cdi_tracking' => $cdi_tracking ), array( 'cdi_order_id' => $order_id ) );
									cdi_c_Gateway::cdi_synchro_gateway_to_order( $order_id );
								}
								//
								// When the filter return is an array with keys, it is a complete return with the following keys:
								//  - 'errorws': an error message (the 3 others keys will be ignored),
								//  - 'tracking': tracking code
								//  - 'parcelnumberpartner': tracking code of the carrier's partner
								//  - 'hreflabel': url to the parcel label
								//
								if ( is_array( $cdi_tracking ) ) {
									$errorws = $cdi_tracking['errorws'] ;
									if ( !$errorws ) {
										$cdi_nbrtrkcode = $cdi_nbrtrkcode + 1;
										cdi_c_Function::cdi_stat( 'CUS-aff' );
										$order_id  = $row->cdi_order_id;
										$cdi_tracking['hreflabel'] = htmlspecialchars_decode( $cdi_tracking['hreflabel'] );
										$cdi_tracking['tracking'] = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $cdi_tracking['tracking'] ) );
										$x = $wpdb->update(
											$wpdb->prefix . 'cdi',
											array(
												'cdi_tracking' => $cdi_tracking['tracking'],
												'cdi_parcelNumberPartner' => $cdi_tracking['parcelnumberpartner'],
												'cdi_hreflabel' => $cdi_tracking['hreflabel'],
											),
											array( 'cdi_order_id' => $order_id )
										);
										cdi_c_Gateway::cdi_synchro_gateway_to_order( $order_id );
									}else{
										break ;
									}
								}
							} // End !$cdi_tracking
						} // End row
					} // End cdi_nbrorderstodo
					$message = number_format_i18n( $cdi_nbrorderstodo ) . __( ' parcels processed ; ', 'cdi' ) . number_format_i18n( $cdi_nbrtrkcode ) . __( ' tracking codes updated.', 'cdi' ) . '  ' . $errorws ;
					update_option( 'cdi_o_notice_display', $message );
					$sendback = admin_url() . 'admin.php?page=passerelle-cdi';
					wp_redirect( $sendback );
					exit;
				} //End $results
			} // End current_user_can
		} // End cdi_custom_run
	} // cdi_gateway_custom
} // End class

