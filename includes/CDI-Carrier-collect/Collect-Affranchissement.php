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
/* Gateway Collect                                                                      */
/****************************************************************************************/

class cdi_c_Collect_Affranchissement {
	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_Collect_run_affranchissement' );
	}

	public static function cdi_Collect_run_affranchissement() {
		if ( isset( $_POST['cdi_gateway_collect'] ) && isset( $_POST['cdi_collect_run_affranchissement_nonce'] ) && wp_verify_nonce( $_POST['cdi_collect_run_affranchissement_nonce'], 'cdi_collect_run_affranchissement' ) ) {
			global $woocommerce;
			global $wpdb;
			global $order_id;
			if ( current_user_can( 'cdi_gateway' ) ) {
				update_option( 'cdi_o_Date_lastwsauto', date( 'ymdHis' ) );
				$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cdi' );
				if ( count( $results ) ) {
					$cdi_nbrorderstodo = 0;
					$cdi_rowcurrentorder = 0;
					$cdi_nbrtrkcode = 0;
					$cdi_nbrwscorrect = 0;
					foreach ( $results as $row ) {
						$order = wc_get_order( $row->cdi_order_id );
						$cdi_tracking = $row->cdi_tracking;
						$carrier = $order->get_meta( '_cdi_meta_carrier' );
						if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'collect' ) ) {
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
						}
					}
					if ( $cdi_nbrorderstodo > 0 ) {
						foreach ( $results as $row ) {
							$order = wc_get_order( $row->cdi_order_id );
							$cdi_tracking = $row->cdi_tracking;
							$carrier = $order->get_meta( '_cdi_meta_carrier' );
							$errorws = null;
							if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'collect' ) ) {
								$cdi_rowcurrentorder = $cdi_rowcurrentorder + 1;
								$array_for_carrier = apply_filters( 'cdi_filterarray_auto_arrayforcarrier', cdi_c_Function::cdi_array_for_carrier( $row ) );
								if ( ! is_array( $array_for_carrier ) ) {
									$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $row->cdi_order_id . ' :  ===> ' . $array_for_carrier;
									break;
								}
								$order_id = $array_for_carrier['order_id'];

								$contractnumber = get_option( 'cdi_installation_id' );
								$retparcelnumber = 'C' . $contractnumber . 'D' . $order_id . 'I';
								$retpdfurl = '';
								$linkto_collectdelivered = admin_url( 'admin-ajax.php' ) . '?action=cdi_collect_delivered&trk=' . $retparcelnumber;

								$pickupfulladdress = $order->get_meta( '_cdi_meta_pickupfulladdress' );
								$pickupLocationId = $order->get_meta( '_cdi_meta_pickupLocationId' );
								$workfilename = trailingslashit( plugin_dir_path( __DIR__ ) ) . '../images/Collect-Label-Model-CDI-A5.pdf';

								$pdf = new FPDI();
								$pdf->AddPage( 'Portrait', array( 100, 150 ) );							
								$pdf->setSourceFile( $workfilename );
								$tplIdx = $pdf->importPage( 1 );
								$pdf->useTemplate( $tplIdx );
								$pdf->SetFont( 'Arial', '', 10 );
								$pdf->SetTextColor( 0, 0, 0 );

								// Logo e-Merchant
								$x = 0;
								$y = 0;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 30, 30 );
								$pdf->SetFont( 'Arial', 'I', 8 );
								$pdf->Text( $x + 10, $y + 15, 'LOGO' );
								$urlcollectlogo = plugins_url( '../images/logocollectmerchant.png', dirname( __FILE__ ) );
								$urlcollectlogo = plugin_dir_path( __FILE__ ) . '../../images/logocollectmerchant.png' ; // path because bug FPDI with CSS
								$urlcollectlogo = apply_filters( 'cdi_filterurl_collect_logo', $urlcollectlogo );
								$pdf->Image( $urlcollectlogo, $x + 1, $y + 1, 28, 28, 'PNG' );

								// E-Merchand
								$x = 30;
								$y = 0;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 70, 30 );
								$pdf->Rect( $x, $y, 12, 4 );
								$pdf->SetFont( 'Arial', 'I', 8 );
								$pdf->Text( $x + 1, $y + 3, 'Shop' );
								$pdf->SetFont( 'Arial', '', 8 );
								$shipperphone = get_option( 'cdi_o_settings_merchant_fixphone' );
								if ( get_option( 'cdi_o_settings_merchant_cellularphone' ) != '' ) {
									$shipperphone = get_option( 'cdi_o_settings_merchant_cellularphone' );
								}
								$pdf->Text( $x + 14, $y + 3, utf8_decode( $shipperphone . '   ' . get_option( 'cdi_o_settings_merchant_Email' ) ) );
								$pdf->SetFont( 'Arial', 'B', 10 );
								$pdf->Text( $x + 5, $y + 8, utf8_decode( get_option( 'cdi_o_settings_merchant_CompanyName' ) ) );
								$pdf->SetFont( 'Arial', '', 8 );
								$pdf->Text( $x + 5, $y + 13, utf8_decode( get_option( 'cdi_o_settings_merchant_Line1' ) ) );
								$pdf->Text( $x + 5, $y + 18, utf8_decode( get_option( 'cdi_o_settings_merchant_Line2' ) ) );
								$pdf->Text( $x + 5, $y + 23, utf8_decode( get_option( 'cdi_o_settings_merchant_ZipCode' ) . ' ' . get_option( 'cdi_o_settings_merchant_City' ) ) );

								// Collect Point
								$x = 0;
								$y = 30;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 70, 30 );
								$pdf->Rect( $x, $y, 12, 4 );
								$pdf->SetFont( 'Arial', 'I', 8 );
								$pdf->Text( $x + 1, $y + 3, 'Collect' );
								$pdf->SetFont( 'Arial', '', 8 );
								if ( isset( $pickupfulladdress['phone'] ) ) {
									$pdf->Text( $x + 14, $y + 3, utf8_decode( $pickupfulladdress['phone'] ) );
								}
								$pdf->Text( $x + 60, $y + 3, utf8_decode( $pickupLocationId ) );
								$pdf->SetFont( 'Arial', 'B', 10 );
								$pdf->Text( $x + 5, $y + 8, utf8_decode( $pickupfulladdress['nom'] ) );
								$pdf->SetFont( 'Arial', '', 8 );
								$pdf->Text( $x + 5, $y + 13, utf8_decode( $pickupfulladdress['adresse1'] ) );
								$t = '';
								if ( isset( $pickupfulladdress['adresse2'] ) ) {
									$t .= utf8_decode( $pickupfulladdress['adresse2'] );
								}
								if ( isset( $pickupfulladdress['adresse3'] ) ) {
									$t .= ' ' . utf8_decode( $pickupfulladdress['adresse3'] );
								}
								$pdf->Text( $x + 5, $y + 18, $t );
								$pdf->Text( $x + 5, $y + 23, utf8_decode( $pickupfulladdress['codePostal'] . ' ' . $pickupfulladdress['localite'] ) );
								$t = '';
								if ( isset( $pickupfulladdress['indice'] ) ) {
									$t .= utf8_decode( $pickupfulladdress['indice'] );
								}
								if ( isset( $pickupfulladdress['parking'] ) ) {
									$t .= ' ' . utf8_decode( $pickupfulladdress['parking'] );
								}
								$pdf->Text( $x + 5, $y + 28, $t );

								// Parcel / Order
								$x = 70;
								$y = 30;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 30, 60 );
								$pdf->Rect( $x, $y, 12, 4 );
								$pdf->SetFont( 'Arial', 'I', 8 );
								$pdf->Text( $x + 1, $y + 3, 'Order' );
								$pdf->SetFont( 'Arial', 'B', 10 );
								$pdf->Text( $x + 2, $y + 8, utf8_decode( '# ' . $order_id . '(' . $array_for_carrier['ordernumber'] . ')' ) );
								$pdf->SetFont( 'Arial', '', 8 );
								$pdf->Text( $x + 2, $y + 13, utf8_decode( get_option( 'cdi_installation_id' ) ) );
								$pdf->Text( $x + 2, $y + 18, utf8_decode( date_format( date_create( $array_for_carrier['order_date'] ), 'Y-m-d H:i:s' ) ) );
								$pdf->Text( $x + 2, $y + 23, utf8_decode( (float)($array_for_carrier['parcel_weight']) / 1000 . ' kg' ) );
								$pdf->SetFont( 'Arial', '', 6 );
								$pdf->Text( $x + 2, $y + 31, utf8_decode( $retparcelnumber ) );
								cdi_c_Pdf_Workshop::cdi_QrCode_pdf( $pdf, $x + 3, $y + 33, $retparcelnumber, 24, 24 );

								// Receiver
								$x = 0;
								$y = 60;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 70, 30 );
								$pdf->Rect( $x, $y, 13, 4 );
								$pdf->SetFont( 'Arial', 'I', 8 );
								$pdf->Text( $x + 1, $y + 3, 'Receiver' );
								$pdf->SetFont( 'Arial', '', 8 );
								$pdf->Text( $x + 14, $y + 3, utf8_decode( $array_for_carrier['billing_phone'] . '   ' . $array_for_carrier['billing_email'] ) );
								$pdf->SetFont( 'Arial', 'B', 10 );
								$pdf->Text( $x + 5, $y + 8, utf8_decode( $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'] ) );
								$pdf->SetFont( 'Arial', '', 8 );
								$pdf->Text( $x + 5, $y + 13, utf8_decode( $array_for_carrier['shipping_address_1'] ) );
								$pdf->Text( $x + 5, $y + 18, utf8_decode( $array_for_carrier['shipping_address_2'] . ' ' . $array_for_carrier['shipping_address_3'] . ' ' . $array_for_carrier['shipping_address_4'] ) );
								$pdf->Text( $x + 5, $y + 23, utf8_decode( $array_for_carrier['shipping_postcode'] . ' ' . $array_for_carrier['shipping_city'] ) );

								// QRCode Acquit
								$x = 0;
								$y = 90;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 50, 50 );
								$pdf->Rect( $x, $y, 14, 4 );
								$pdf->SetFont( 'Arial', 'I', 8 );
								$pdf->Text( $x + 1, $y + 3, 'Delivered' );
								$pdf->SetFont( 'Arial', 'I', 8 );
								$pdf->Text( $x + 15, $y + 3, utf8_decode( 'after pickup by the receiver' ) );
								cdi_c_Pdf_Workshop::cdi_QrCode_pdf( $pdf, $x + 3, $y + 5, $linkto_collectdelivered, 44, 44 );

								// Future use
								$x = 50;
								$y = 90;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 50, 50 );
								/*
								$pdf->Rect($x, $y, 14, 4) ;
								$pdf->SetFont('Arial','I',8);
								$pdf->Text($x+1,$y+3,'Delivered');
								$pdf->SetFont('Arial','I',8);
								$pdf->Text($x+15,$y+3,utf8_decode ('after pickup by the receiver'));
								cdi_c_Pdf_Workshop::cdi_QrCode_pdf($pdf, $x+3, $y+5, $linkto_atcollectpoint, 44, 44) ;
								*/

								// Option for old equipments
								// EAN128 tracking
								$x = 0;
								$y = 140;  // x=col y=line
								$pdf->SetXY( $x, $y );
								$pdf->Rect( $x, $y, 100, 10 );
								// $pdf->Rect($x, $y, 12, 4) ;
								// $pdf->SetFont('Arial','I',8);
								// $pdf->Text($x+1,$y+3,'Tracking');
								// $pdf->SetFont('Arial','',8);
								// $pdf->Text($x+15,$y+3,utf8_decode ($retparcelnumber));
								cdi_c_Pdf_Workshop::cdi_EAN128_pdf( $pdf, $x + 2, $y + 2, $retparcelnumber, 95, 6 );

								$resultpdf = $pdf->Output( 'S' );
								$cdi_nbrwscorrect = $cdi_nbrwscorrect + 1;
								cdi_c_Function::cdi_stat( 'CAC-aff' );
								$x = $wpdb->update(
									$wpdb->prefix . 'cdi',
									array(
										'cdi_tracking' => $retparcelnumber,
										'cdi_parcelNumberPartner' => '',
										'cdi_hreflabel' => $retpdfurl,
									),
									array( 'cdi_order_id' => $order_id )
								);
								$base64label = base64_encode( $resultpdf );
								cdi_c_Function::cdi_uploads_put_contents( $order_id, 'label', $base64label );
								cdi_c_Gateway::cdi_synchro_gateway_to_order( $order_id );

								$statutinit = get_option( 'cdi_o_settings_collect_startingstatus' );
								$order->update_meta_data( '_cdi_meta_collect_status', $statutinit );
								$security = get_option( 'cdi_o_settings_collect_securitycode' );
								$order->update_meta_data( '_cdi_meta_securitymode', $security );
								$pickup_Location_id = $order->get_meta( '_cdi_meta_pickupLocationId' );
								$order->update_meta_data( '_cdi_meta_deliveredcode', $pickup_Location_id ); // the default
								$order->save();

								// ********************************* End Collect Web service *********************************
							} // End !$cdi_tracking
							if ( $errorws !== null ) {
								break;
							}
						} // End row
						// Close sequence
						$message = number_format_i18n( $cdi_nbrwscorrect ) . __( ' parcels processed with Collect Service.', 'cdi' ) . ' ' . $errorws;
						update_option( 'cdi_o_notice_display', $message );
						$sendback = admin_url() . 'admin.php?page=passerelle-cdi';
						wp_redirect( $sendback );
						exit();
					} // End cdi_nbrorderstodo
				} //End $results
			} // End current_user_can
		} // End cdi_Collect_run_affranchissement
	} // cdi_gateway_Collect
} // End class




