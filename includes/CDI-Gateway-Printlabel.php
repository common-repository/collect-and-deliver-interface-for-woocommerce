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
/* Gateway printlabel - print labels in a pdf file                                      */
/****************************************************************************************/
class cdi_c_Gateway_Printlabel {
	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_printlabel_run' );
	}

	public static function cdi_printlabel_run() {
		if ( isset( $_POST['cdi_gateway_printlabel'] ) && isset( $_POST['cdi_printlabel_run_nonce'] ) && wp_verify_nonce( $_POST['cdi_printlabel_run_nonce'], 'cdi_printlabel_run' ) ) {
			global $woocommerce;
			global $wpdb;
			if ( current_user_can( 'cdi_gateway' ) ) {
				$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cdi' );
				if ( count( $results ) ) {
					$cdi_nbrorderstodo = 0;
					$cdi_rowcurrentorder = 0;
					$cdi_nbrtrkcode = 0;
					$arrayorderid = array();
					foreach ( $results as $row ) {
						$cdi_tracking = $row->cdi_tracking;
						if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) ) {
							$arrayorderid[] = $row->cdi_order_id;
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
						}
					}
					if ( $cdi_nbrorderstodo > 0 ) {
						$pagesize = get_option( 'cdi_o_settings_pagesize' );
						$pagesize = str_replace( ' ', '', $pagesize );
						$labellayout = get_option( 'cdi_o_settings_labellayout' );
						$labellayout = str_replace( ' ', '', $labellayout );
						$addresswidth = get_option( 'cdi_o_settings_addresswidth' );
						$fontsize = get_option( 'cdi_o_settings_fontsize' );
						$startrank = get_option( 'cdi_o_settings_startrank' );
						$managerank = get_option( 'cdi_o_settings_managerank' );
						$testgrid = get_option( 'cdi_o_settings_testgrid' );
						$miseenpage = get_option( 'cdi_o_settings_miseenpage' );
						$customcss = get_option( 'cdi_o_settings_customcss' );
						$arrx = explode( 'x', $pagesize );
						$pagewidth = $arrx[0];
						$pageheight = $arrx[1];
						$arrx = explode( 'x', $labellayout );
						$cols = $arrx[0];
						$rows = $arrx[1];
						$labelwidth = $pagewidth / $cols;
						$labelheight = $pageheight / $rows;
						$order_count = $cdi_nbrorderstodo;
						$labels_per_page = $cols * $rows;
						$page_count = ceil( ( $order_count + $startrank - 1 ) / $labels_per_page );
						$label_number = 0;
						?>
			<!DOCTYPE html>
			<html>
			<head>
			<title><?php _e( 'Printing Adress Labels', 'cdi' ); ?></title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<style type="text/css">
			  @page { margin: 0; }
			  html, body {margin: 0; padding: 0; border: none; height: 100%; font-size: <?php echo esc_attr( $fontsize ); ?>; font-family: sans-serif; }
			  table { border-collapse: collapse; page-break-after: always; }
			  .label-wrapper { max-height: <?php echo esc_attr( $labelheight ); ?>mm; max-width: <?php echo esc_attr( $labelwidth ); ?>mm; overflow: hidden; }
			  td.label { vertical-align: middle; padding: 0; border: 1px solid black; }
			  .address-block { width: <?php echo esc_attr( $addresswidth ); ?>; margin: auto; text-align: left; clear:both; }
			  .address_show { float: left; margin-top:5px; width: 100%; }
			  .clearb{ 	clear:both; }
						<?php
						if ( $testgrid !== 'yes' ) {
							  echo ' td.label {border-style:none !important; }';
						}
						echo ' body {' . esc_attr( $miseenpage ) . ' }';
						// Model miseenpage to adapt : padding: 0 !important; margin-top: 5mm !important; height:96% !important; margin-left: 12mm !important; width:99% !important;
						echo esc_attr( $customcss );
						?>
			  ;
			</style>
			</head>
			<body id="bodycdi">
						<?php
						$PNG_WEB_DIR = plugin_dir_path( __FILE__ ) . 'temp/';
						wp_mkdir_p( $PNG_WEB_DIR );

						for ( $page = 0; $page < $page_count; $page++ ) {
							  echo '<table class="address-labels" width="100%" height="100%" border="0" cellpadding="0">';
							  $last_height = 0;
							  $current_height = $current_width = 0;
							  $current_row = 0;
							for ( $label = 0; $label < $labels_per_page; $label++ ) {
								$label_number++;
								$current_col = ( ( $label_number - 1 ) % $cols ) + 1;
								if ( $current_col == 1 ) {
									$last_height = $current_height;
									$last_width = 0;
									$current_row++;
									echo '<tr class="label-row">';
								}
								// Get de data to print
								if ( $label_number > $cdi_nbrorderstodo + $startrank - 1 ) {
									$label_data = ''; // Fill labels out of scope with blanc
								} else {
									if ( $label_number >= $startrank ) {
										  cdi_c_Function::cdi_stat( 'ETI-aff' );
										  $order_id = $arrayorderid[ $label_number - $startrank ];
										  cdi_c_Function::cdi_debug( __LINE__, __FILE__, $order_id, 'msg' );
										  $array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );
										  $label_data  = '<div style="width:100%;">';
										  $comp = apply_filters( 'cdi_filterstring_gateway_companyandorderid', $array_for_carrier['shipping_company'] . ' - ' . $array_for_carrier['order_id'] . '(' . $array_for_carrier['ordernumber'] . ')', $array_for_carrier );
										  $label_data .= '<div id="printlabelref" style="font-size:50%; text-align:right;">(' . $comp . ')</div>';
										if ( $array_for_carrier['shipping_company'] !== $array_for_carrier['shipping_last_name'] ) {
											$label_data .= strtoupper( $array_for_carrier['shipping_company'] ) . '<br/>';
										}
										$label_data .= $array_for_carrier['shipping_first_name'] . ' ' . strtoupper( $array_for_carrier['shipping_last_name'] ) . '<br/>';
										$label_data .= $array_for_carrier['shipping_address_1'] . '<br/>';
										if ( $array_for_carrier['shipping_address_2'] !== '' ) {
														  $label_data .= $array_for_carrier['shipping_address_2'] . '<br/>';
										}
										if ( $array_for_carrier['shipping_address_3'] !== '' ) {
													  $label_data .= $array_for_carrier['shipping_address_3'] . '<br/>';
										}
										if ( $array_for_carrier['shipping_address_4'] !== '' ) {
												  $label_data .= $array_for_carrier['shipping_address_4'] . '<br/>';
										}
										$label_data .= $array_for_carrier['shipping_postcode'] . ' ' . strtoupper( $array_for_carrier['shipping_city_state'] ) . '<br/>';
										$countryname = mb_strtoupper( WC()->countries->countries[ $array_for_carrier['shipping_country'] ] );
										$countryname = cdi_c_Function::cdi_str_to_noaccent( $countryname );
										$countryname = str_replace( ' (USA)', '', $countryname );
										$label_data .= $countryname . '<br/>';
										$label_data .= '<div>';
										$label_data = apply_filters( 'cdi_filterhtml_printlabel_labeldata', $label_data, $array_for_carrier );
									} else {
										$label_data = ''; // Fill labels out of scope with blanc
									}
								}
								$current_width = round( $current_col * ( 100 / $cols ) );
								$width = $current_width - $last_width;
								$last_width = $current_width;
								$current_height = round( $current_row * ( 100 / $rows ) );
								$height = $current_height - $last_height;
								printf( '<td width="%s%%" height="%s%%" class="label"><div class="label-wrapper">', $width, $height );
								echo '<div class="address-block">';
								echo '<div class="address_show">';
								echo wp_kses_post( $label_data );
								echo '</div>';
								echo '<div class="clearb"></div>';
								echo '</div>';
								echo '</div></td>';
								if ( $current_col == $cols ) {
													echo '</tr>';
								}
							}
							echo '</table>';
						}
						?>
			</body>
			</html>
						<?php
						if ( $managerank == 'forward' ) {
							$startrank = ( ( $startrank + $order_count - 1 ) % $labels_per_page ) + 1;
							update_option( 'cdi_o_settings_startrank', $startrank );
						}

						// Eventually a  window.print() here

					} // End cdi_nbrorderstodo
					$message = number_format_i18n( $cdi_nbrorderstodo ) . __( ' address labels have been created.', 'cdi' );
					update_option( 'cdi_o_notice_display', $message );
					$sendback = admin_url() . 'admin.php?page=passerelle-cdi';
					if ( $cdi_nbrorderstodo > 0 ) {
						wp_die();
					} else {
						// If empty, close current tab and die, which return to calling tab
						echo "<script type='text/javascript'> window.close(); </script>";
						wp_die();
					}
				} //End $results
			} // End current_user_can
		} // End printlabel run
	} // cdi_gateway_printlabel
} // End class
?>
