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
/* Gateway manual - product a csv file                                                  */
/****************************************************************************************/
class cdi_c_Gateway_Manual {
	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_manual_run' );
	}
	public static function cdi_manual_run() {
		if ( isset( $_POST['cdi_gateway_manual'] ) && isset( $_POST['cdi_manual_run_nonce'] ) && wp_verify_nonce( $_POST['cdi_manual_run_nonce'], 'cdi_manual_run' ) ) {
			global $woocommerce;
			global $wpdb;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cdi' );
				if ( count( $results ) ) {
					$cdi_nbrorderstodo = 0;
					$cdi_rowcurrentorder = 0;
					$cdi_nbrtrkcode = 0;
					foreach ( $results as $row ) {
						$cdi_tracking = $row->cdi_tracking;
						if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) ) {
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
						}
					}
					if ( $cdi_nbrorderstodo > 0 ) {
						$out = fopen( 'php://output', 'w' );
						$thecsvfile = 'CDI-Orders-' . date( 'YmdHis' ) . '.csv';
						header( 'Content-type: text/csv' );
						header( 'Content-Disposition: inline; filename=' . $thecsvfile );
						foreach ( $results as $row ) {
							  $cdi_tracking = $row->cdi_tracking;
							if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) ) {
								$cdi_rowcurrentorder = $cdi_rowcurrentorder + 1;
								cdi_c_Function::cdi_stat( 'MAN-aff' );
								$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $row );
								cdi_c_Function::cdi_debug( __LINE__, __FILE__, $array_for_carrier['order_id'], 'msg' );
								// Open sequence
								if ( $cdi_rowcurrentorder == 1 ) {
									$storestringtitle = '';
									$storearraystringrow = array();
								}
								// Compute current csv header for the first line csv
								$stringtitle = '';
								foreach ( $array_for_carrier as $key => $keyvalue ) {
									$stringtitle = $stringtitle . "'" . $key . "'" . ',';
								}
								$stringtitle = str_replace( "'", '', $stringtitle );
								$stringtitle = $stringtitle . "\r\n";
								if ( strlen( $stringtitle ) > strlen( $storestringtitle ) ) {
									$storestringtitle = $stringtitle; // Choose the longest header because of variable CN23 articles
								}
								// Compute current row and store it
								$stringrow = '';
								foreach ( $array_for_carrier as $key => $keyvalue ) {
									if ( is_array( $keyvalue ) ) {
										$keyvalue = implode( ' ', $keyvalue );
									}
									$keyvalue = trim( $keyvalue, ' ' );
									$keyvalue = str_replace( ',', ' ', $keyvalue ); // suppress , to be compatible with , csv delimiter
									$stringrow = $stringrow . $keyvalue . ',';
								}
								$stringrow = $stringrow . "\r\n";
								$storearraystringrow[] = $stringrow;
								// Close sequence : Only at that point is done the real writing of csv
								if ( $cdi_rowcurrentorder == $cdi_nbrorderstodo ) {
									fwrite( $out, $storestringtitle );
									foreach ( $storearraystringrow as $stringrow ) {
										fwrite( $out, $stringrow );
									}
								}
							} // End !$cdi_tracking
						} // End row
						fclose( $out );
						$message = number_format_i18n( $cdi_nbrorderstodo ) . __( ' parcels inserted in csv file.', 'cdi' );
						update_option( 'cdi_o_notice_display', $message );
						$sendback = admin_url() . 'admin.php?page=passerelle-cdi';
						// wp_redirect($sendback); // Dont work because header - another way to find
						exit();
					} // End cdi_nbrorderstodo
				} //End $results
			} // End current_user_can
		} // End manual run
	} // cdi_gateway_manual
} // End class

