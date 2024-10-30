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
/* Gateway Mondial Relay                                                                       */
/****************************************************************************************/

class cdi_c_Mondialrelay_Affranchissement {
	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_Mondialrelay_run_affranchissement' );
	}

	public static function cdi_Mondialrelay_run_affranchissement() {
		if ( isset( $_POST['cdi_gateway_mondialrelay'] ) && isset( $_POST['cdi_mondialrelay_run_affranchissement_nonce'] ) && wp_verify_nonce( $_POST['cdi_mondialrelay_run_affranchissement_nonce'], 'cdi_mondialrelay_run_affranchissement' ) ) {
			global $woocommerce;
			global $wpdb;
			global $order_id;
			global $MRerrcodlist;
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
						if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'mondialrelay' ) ) {
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
						}
					}
					if ( $cdi_nbrorderstodo > 0 ) {
						foreach ( $results as $row ) {
							$order = wc_get_order( $row->cdi_order_id );
							$cdi_tracking = $row->cdi_tracking;
							$carrier = $order->get_meta( '_cdi_meta_carrier' );
							$errorws = null;
							if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'mondialrelay' ) ) {
								$cdi_rowcurrentorder = $cdi_rowcurrentorder + 1;
								$array_for_carrier = apply_filters( 'cdi_filterarray_auto_arrayforcarrier', cdi_c_Function::cdi_array_for_carrier( $row ) );
								if ( ! is_array( $array_for_carrier ) ) {
									$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $row->cdi_order_id . ' :  ===> ' . $array_for_carrier;
									break;
								}
								$order_id = $array_for_carrier['order_id'];

								$MR_WebSiteId = get_option( 'cdi_o_settings_mondialrelay_contractnumber' );
								$MR_WebSiteKey = get_option( 'cdi_o_settings_mondialrelay_password' );

								$client = new nusoap_client( 'https://api.mondialrelay.com/Web_Services.asmx?WSDL', true );
								$client->soap_defencoding = 'utf-8';

								$product_code = $array_for_carrier['product_code'];
								$pickup_Location_id = $array_for_carrier['pickup_Location_id'];
								$shippingcountry = $array_for_carrier['shipping_country'];
								if ( null == $product_code or $product_code == '' ) {
									if ( ! ( strpos( get_option( 'cdi_o_settings_mondialrelay_Gp1CountryCodes' ), $shippingcountry ) === false ) ) {
										$switch = 'Gp1';
									} elseif ( ! ( strpos( get_option( 'cdi_o_settings_mondialrelay_Gp2CountryCodes' ), $shippingcountry ) === false ) ) {
										$switch = 'Gp2';
									} elseif ( ! ( strpos( get_option( 'cdi_o_settings_mondialrelay_Gp3CountryCodes' ), $shippingcountry ) === false ) ) {
										$switch = 'Gp3';
									} else {
										$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $array_for_carrier['order_id'] . ' - ' . 'Mondial Relay' . ' : ' . __( 'Shipping country not valid', 'cdi' ) . ' ===> ' . $shippingcountry;
										break;
									}
									switch ( $switch ) {
										case 'Gp1':
											$arrayproductcode = explode( ',', get_option( 'cdi_o_settings_mondialrelay_Gp1ProductCodes' ) );
											if ( $pickupLocationId ) {
													$product_code = $arrayproductcode[2];
											} else {
												if ( $array_for_carrier['signature'] == 'yes' ) {
													$product_code = $arrayproductcode[1];
												} else {
													$product_code = $arrayproductcode[0];
												}
											}
											break;
										case 'Gp2':
											$arrayproductcode = explode( ',', get_option( 'cdi_o_settings_mondialrelay_Gp2ProductCodes' ) );
											if ( $pickupLocationId ) {
												$product_code = $arrayproductcode[2];
											} else {
												if ( $array_for_carrier['signature'] == 'yes' ) {
													$product_code = $arrayproductcode[1];
												} else {
													$product_code = $arrayproductcode[0];
												}
											}
											break;
										case 'Gp3':
											$arrayproductcode = explode( ',', get_option( 'cdi_o_settings_mondialrelay_Gp3ProductCodes' ) );
											if ( $pickupLocationId ) {
												$product_code = $arrayproductcode[2];
											} else {
												if ( $array_for_carrier['signature'] == 'yes' ) {
													$product_code = $arrayproductcode[1];
												} else {
													$product_code = $arrayproductcode[0];
												}
											}
											$insurancevalue = 0;
											break;
									} // End switch
								}

								if ( $errorws !== null ) {
									break;
								}
								$Enseigne = $MR_WebSiteId;

								$ModeCol = $array_for_carrier['parcel_collect'];
								if ( ! $ModeCol ) {
									$ModeCol = get_option( 'cdi_o_settings_mondialrelay_collect' );
								}
								$ModeLiv = $product_code;
								$NDossier = $array_for_carrier['sender_parcel_ref'] . '-' . $array_for_carrier['ordernumber'];
								$NClient = $array_for_carrier['sender_parcel_ref'];

								$Expe_Langage = 'FR';
								$Expe_Ad1 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_CompanyName' ) ) );
								$Expe_Ad2 = '';
								$Expe_Ad3 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_Line1' ) ) );
								$Expe_Ad4 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_Line2' ) ) );
								$Expe_Ville = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_City' ) ) );
								$Expe_CP = get_option( 'cdi_o_settings_merchant_ZipCode' );
								$Expe_Pays = get_option( 'cdi_o_settings_merchant_CountryCode' );

								$Expe_Tel1 = get_option( 'cdi_o_settings_merchant_fixphone' );
								$Expe_Tel2 = get_option( 'cdi_o_settings_merchant_cellularphone' );
								$Expe_Mail = get_option( 'cdi_o_settings_merchant_Email' );

								$Dest_Langage = 'FR';
								$Dest_Ad1 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'] ) );
								$Dest_Ad2 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_address_2'] ) );
								$Dest_Ad3 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_address_1'] ) );
								$Dest_Ad4 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_address_3'] . ' ' . $array_for_carrier['shipping_address_4'] ) );
								$Dest_Ville = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_city_state'] ) );
								$Dest_CP = $array_for_carrier['shipping_postcode'];
								$Dest_Pays = $array_for_carrier['shipping_country'];

								$PhoneNumber = cdi_c_Function::cdi_sanitize_phone( $array_for_carrier['billing_phone'] );
								$Dest_Tel1 = $PhoneNumber;
								$MobileNumber = cdi_c_Function::cdi_sanitize_mobilenumber( $array_for_carrier['billing_phone'], $array_for_carrier['shipping_country'] );
								$MobileNumber = apply_filters( 'cdi_filterstring_auto_mobilenumber', $MobileNumber, $order_id );
								if ( isset( $MobileNumber ) && $MobileNumber !== '' ) {
									$Dest_Tel2 = $MobileNumber; // Set only if it is a mobile
								} else {
									$Dest_Tel2 = '';
								}
								$Dest_Mail = $array_for_carrier['billing_email'];

								$Poids = $array_for_carrier['parcel_weight'];
								$Longueur = '';
								$Taille = '';
								$NbColis = 1;

								$CRT_Valeur = 0; // Settings valeur contre remboursement
								$CRT_Devise = 'EUR';

								$num = $array_for_carrier['cn23_shipping'];
								$Exp_Valeur =  (float)(str_replace (',', '.', $num)) * 100 ;
								$Exp_Devise = 'EUR';

								$COL_Rel_Pays = 'XX';
								$COL_Rel = 'AUTO';
								$LIV_Rel_Pays = $array_for_carrier['shipping_country'];

								$TAvisage = '';
								$TReprise = 'N';
								$Montage = 0;
								$TRDV = '';

								// Always choose the most protective insurance code
								$additional_compensation = $array_for_carrier['additional_compensation'];
								$compensation_amount = $array_for_carrier['compensation_amount'];
								$Assurance = 0;
								if ( $additional_compensation == 'yes' and is_numeric( $compensation_amount ) and $compensation_amount > 0 ) {
									$Assurance = 1;
									if ( $compensation_amount > 50 ) {
										$Assurance = 2;
									}
									if ( $compensation_amount > 125 ) {
													  $Assurance = 3;
									}
									if ( $compensation_amount > 250 ) {
													$Assurance = 4;
									}
									if ( $compensation_amount > 375 ) {
												  $Assurance = 5;
									}
								}

								$Instructions = $array_for_carrier['carrier_instructions'];

								$params = array(
									'Enseigne' => $Enseigne,
									'ModeCol' => $ModeCol,
									'ModeLiv' => $ModeLiv,
									'NDossier' => $NDossier,
									'NClient' => $NClient,
									'Expe_Langage' => $Expe_Langage,
									'Expe_Ad1' => $Expe_Ad1,
									'Expe_Ad2' => $Expe_Ad2,
									'Expe_Ad3' => $Expe_Ad3,
									'Expe_Ad4' => $Expe_Ad4,
									'Expe_Ville' => $Expe_Ville,
									'Expe_CP' => $Expe_CP,
									'Expe_Pays' => $Expe_Pays,
									'Expe_Tel1' => $Expe_Tel1,
									'Expe_Tel2' => $Expe_Tel2,
									'Expe_Mail' => $Expe_Mail,
									'Dest_Langage' => $Dest_Langage,
									'Dest_Ad1' => $Dest_Ad1,
									'Dest_Ad2' => $Dest_Ad2,
									'Dest_Ad3' => $Dest_Ad3,
									'Dest_Ad4' => $Dest_Ad4,
									'Dest_Ville' => $Dest_Ville,
									'Dest_CP' => $Dest_CP,
									'Dest_Pays' => $Dest_Pays,
									'Dest_Tel1' => $Dest_Tel1,
									'Dest_Tel2' => $Dest_Tel2,
									'Dest_Mail' => $Dest_Mail,
									'Poids' => $Poids,
									'Longueur' => $Longueur,
									'Taille' => $Taille,
									'NbColis' => $NbColis,
									'CRT_Valeur' => $CRT_Valeur,
									'CRT_Devise' => $CRT_Devise,
									'Exp_Valeur' => $Exp_Valeur,
									'Exp_Devise' => $Exp_Devise,
									'COL_Rel_Pays' => $COL_Rel_Pays,
									'COL_Rel' => $COL_Rel,
									'LIV_Rel_Pays' => $LIV_Rel_Pays,
									'LIV_Rel' => $pickup_Location_id,
									'TAvisage' => $TAvisage,
									'TReprise' => $TReprise,
									'Montage' => $Montage,
									'TRDV' => $TRDV,
									'Assurance' => $Assurance,
									'Instructions' => $Instructions,
								);
								$code = implode( '', $params );
								$code .= $MR_WebSiteKey;
								$params['Security'] = strtoupper( md5( $code ) );
								// Texte additionnel au label. Ne fonctionne que pour les formats A5 et 10x15
								// Limité à 10 lignes x 30 caractères séparés par "(cr)". Exemple "première ligne(cr)deuxième ligne(cr)troisème ligne"
								$addmerchanttext = '';
								$addmerchanttext = apply_filters( 'cdi_filterstring_MR_addlabeltext', $addmerchanttext, 'aller', $order_id, $array_for_carrier );
								$params['Texte'] = $addmerchanttext;

								$result = $client->call(
									'WSI2_CreationEtiquette',
									$params,
									'https://api.mondialrelay.com/',
									'https://api.mondialrelay.com/WSI2_CreationEtiquette'
								);

								if ( $client->fault ) {
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client, 'tec' );

										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->request, 'tec' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->response, 'tec' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->getDebug(), 'tec' );

										$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $array_for_carrier['order_id'] . ' - Mondial Relay : Fault (Expect -The request contains an invalid SOAP body)';
								} else {
									$err = $client->getError();
									if ( $err ) {
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $err, 'tec' );
										$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $array_for_carrier['order_id'] . ' - ' . $err ;
									} else {
										$errorid = $result['WSI2_CreationEtiquetteResult']['STAT'];
										// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $result, 'tec');
										if ( $errorid != 0 ) {
											cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
											cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
											$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $array_for_carrier['order_id'] . ' - ' . $errorid . ' : ' . $MRerrcodlist[ $errorid ];
										} else {
											$retparcelnumber = $result['WSI2_CreationEtiquetteResult']['ExpeditionNum'];
											$parcelNumberPartner = '';
											$urllabelMR = $result['WSI2_CreationEtiquetteResult']['URL_Etiquette'];  // received in A4 format
											$format = get_option( 'cdi_o_settings_mondialrelay_OutputPrintingType' );
											if ( $format == 'PDF_10x15_300dpi' ) {
												$urllabelMR = str_replace( 'format=A4', 'format=10x15', $urllabelMR );
											}
											if ( $format == 'PDF_A5_paysage' ) {
												$urllabelMR = str_replace( 'format=A4', 'format=A5', $urllabelMR );
											}
											$retpdfurl = 'https://www.mondialrelay.com' . $urllabelMR;
											cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Order : ' . $order_id . ' Parcel : ' . $retparcelnumber, 'msg' );
											$cdi_nbrwscorrect = $cdi_nbrwscorrect + 1;

											if ( get_option( 'cdi_o_settings_mondialrelay_contractnumber' ) !== 'BDTEST13' ) {
												cdi_c_Function::cdi_stat( 'MON-aff' );
											} else {
												cdi_c_Function::cdi_stat( 'MON-aff-test' );
											}
											$x = $wpdb->update(
												$wpdb->prefix . 'cdi',
												array(
													'cdi_tracking' => $retparcelnumber,
													'cdi_parcelNumberPartner' => $parcelNumberPartner,
													'cdi_hreflabel' => $retpdfurl,
												),
												array( 'cdi_order_id' => $order_id )
											);

											$args = array(
												'method' => 'GET',
												'timeout' => 8,
												'blocking' => true,
												'headers' => array( 'Content-Type' => 'Content-type: application/pdf' ),
											);
											$label = wp_remote_get( $retpdfurl, $args )['body'];
											if ( substr( $label, 0, 4 ) !== '%PDF' ) { // Not a pdf
												// PDF palliative pending evolution by MR  if not directly a pdf: Create an simple information PDF with a link to Mondial Relay label.
												$workfilename = trailingslashit( plugin_dir_path( __DIR__ ) ) . '../images/MR-Label-Model-clean.pdf';
												$pdf = new FPDI();
												$pdf->AddPage( 'Portrait', array( 100, 150 ) );
												$pdf->setSourceFile( $workfilename );
												$tplIdx = $pdf->importPage( 1 );
												$pdf->useTemplate( $tplIdx );
												$pdf->SetFont( 'Arial', '', 12 );
												$pdf->SetTextColor( 0, 0, 0 );
												// Message
												$x = 5;
												$y = 30;  // x=col y=line
												$pdf->SetXY( $x, $y );
												$pdf->SetFont( 'Arial', '', 10 );
												$pdf->Text( $x, $y + 0, utf8_decode( 'Mondial Relay ne permettant pas de téléchargement ' ) );
												$pdf->Text( $x, $y + 5, utf8_decode( "direct des PDF d'étiquettes, vous devez suivre le lien " ) );
												$pdf->Text( $x, $y + 10, utf8_decode( "ci-dessous pour acquérir l'étiquette * : " . $retparcelnumber ) );
												// link
												$x = 5;
												$y = 50;  // x=col y=line
												$pdf->SetXY( $x, $y );
												$pdf->SetTextColor( 0, 0, 255 );
												$pdf->SetFont( 'Arial', 'B', 12 );
												$pdf->Text( $x + 20, $y + 5, utf8_decode( 'Etiquette ' . $retparcelnumber ) );
												$pdf->Link( $x, $y, 90, 10, $retpdfurl );
												$pdf->SetFont( 'Arial', 'I', 7 );
												$pdf->SetTextColor( 0, 0, 0 );
												$x = 5;
												$y = 140;  // x=col y=line
												$pdf->Text( $x + 20, $y + 5, utf8_decode( "(*) Situation susceptible d'évoution à l'avenir." ) );
												$label = $pdf->Output( 'S' );
											}

											$base64label = base64_encode( $label );
											cdi_c_Function::cdi_uploads_put_contents( $order_id, 'label', $base64label );
											cdi_c_Gateway::cdi_synchro_gateway_to_order( $order_id );
										}
									}
								}
								// ********************************* End Mondialrelay Web service *********************************
							} // End !$cdi_tracking
							if ( $errorws !== null ) {
								break;
							}
						} // End row
						// Close sequence
						$message = number_format_i18n( $cdi_nbrwscorrect ) . __( ' parcels processed with Mondialrelay Web Service.', 'cdi' ) . ' ' . $errorws;
						update_option( 'cdi_o_notice_display', $message );
						$sendback = admin_url() . 'admin.php?page=passerelle-cdi';
						wp_redirect( $sendback );
						exit();
					} // End cdi_nbrorderstodo
				} //End $results
			} // End current_user_can
		} // End cdi_Mondialrelay_run_affranchissement
	} // cdi_gateway_Mondialrelay
} // End class




