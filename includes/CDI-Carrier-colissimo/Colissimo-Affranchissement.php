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
/* Gateway auto                                                                       */
/****************************************************************************************/

class cdi_c_Colissimo_Affranchissement {
	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_Colissimo_run_affranchissement' );
	}

	public static function cdi_Colissimo_run_affranchissement() {
		if ( isset( $_POST['cdi_gateway_colissimo'] ) && isset( $_POST['cdi_Colissimo_run_affranchissement_nonce'] ) && wp_verify_nonce( $_POST['cdi_Colissimo_run_affranchissement_nonce'], 'cdi_Colissimo_run_affranchissement' ) ) {
			global $woocommerce;
			global $wpdb;
			define("SERVER_NAME", 'https://ws.colissimo.fr'); 
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
						if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'colissimo' ) ) {
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
						}
					}
					if ( $cdi_nbrorderstodo > 0 ) {
						$local_wsdl_preload = cdi_c_Function::cdi_url_get_remote('https://ws.colissimo.fr/sls-ws/SlsServiceWS/2.0?wsdl') ; 
						if ($local_wsdl_preload) {
							$local_wsdl_preload = $local_wsdl_preload['body'] ;
						}						
						if ( !strpos($local_wsdl_preload, '</wsdl:definitions>')) { // Test if end of the wsdl file exist
							cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , 'La Poste WS not ready. Wsdl:definitions can not be fully loaded. Please try again', 'tec');
							$errorws = __(' ===> Error stop processing : ','cdi') . "The LaPoste Web Service is not operational at this time. See logs for more information. Please try later or contact your advisor at La Poste.";
							cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $local_wsdl_preload, 'exp');
						}else{ // WS is OK													
							sleep( 1 );
						foreach ( $results as $row ) {
							$order = wc_get_order( $row->cdi_order_id );
							$cdi_tracking = $row->cdi_tracking;
							$carrier = $order->get_meta( '_cdi_meta_carrier' );
							$errorws = null;
							if ( ! $cdi_tracking && ( $row->cdi_status == 'open' or null == $row->cdi_status ) && ( $carrier == 'colissimo' ) ) {
								$cdi_rowcurrentorder = $cdi_rowcurrentorder + 1;
								$array_for_carrier = apply_filters( 'cdi_filterarray_auto_arrayforcarrier', cdi_c_Function::cdi_array_for_carrier( $row ) );
								if ( ! is_array( $array_for_carrier ) ) {
									$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $row->cdi_order_id . ' :  ===> ' . $array_for_carrier;
									break;
								}
								if ( $cdi_rowcurrentorder == 1 ) {
									// Open sequence
								}
								// ********************************* Begin Colissimo Web service *********************************
								// Document Technique - Version Juillet 2022 - Spécifications du Web Service d’Affranchissement Colissimo
								// Direct use of SOAP, use of WS Colissimo 2.0, https exchanges, SOAP at 1.0
								
								sleep( 1 ); // To avoid overloadingf LaPoste
								$order_id = $array_for_carrier['order_id'];
															
								// ********************************* Compute parameters  ******************************
								$compensation_amount = $array_for_carrier['compensation_amount'];
								if ( ! is_numeric( $compensation_amount ) ) {
									$compensation_amount = 0;
								} else {
									$compensation_amount = $compensation_amount * 100;
								}
								if ( $array_for_carrier['additional_compensation'] == 'no' ) {
									$compensation_amount = 0;
								}
								$insurancevalue = $compensation_amount;
								$productcode = $order->get_meta( '_cdi_meta_productCode' );
								$pickupLocationId = $order->get_meta( '_cdi_meta_pickupLocationId' );
								$shippingcountry = $array_for_carrier['shipping_country'];
								if ( null == $productcode or $productcode == '' ) {
									if ( ! ( strpos( get_option( 'cdi_o_settings_colissimo_FranceCountryCodes' ), $shippingcountry ) === false ) ) {
										$switch = 'france';
									} elseif ( ! ( strpos( get_option( 'cdi_o_settings_colissimo_OutreMerCountryCodes' ), $shippingcountry ) === false ) ) {
										$switch = 'outremer';
									} elseif ( ! ( strpos( get_option( 'cdi_o_settings_colissimo_EuropeCountryCodes' ), $shippingcountry ) === false ) ) {
										$switch = 'europe';
									} else {
										 $switch = 'international';
									}
									switch ( $switch ) {
										case 'france':
											$arrayproductcode = explode( ',', get_option( 'cdi_o_settings_colissimo_FranceProductCodes' ) );
											if ( $pickupLocationId ) {
												$productcode = $arrayproductcode[2];
											} else {
												if ( $array_for_carrier['signature'] == 'yes' ) {
													$productcode = $arrayproductcode[1];
												} else {
													$productcode = $arrayproductcode[0];
												}
											}
											break;
										case 'outremer':
											$arrayproductcode = explode( ',', get_option( 'cdi_o_settings_colissimo_OutreMerProductCodes' ) );
											if ( $pickupLocationId ) {
												$productcode = $arrayproductcode[2];
											} else {
												if ( $array_for_carrier['signature'] == 'yes' ) {
													$productcode = $arrayproductcode[1];
												} else {
													$productcode = $arrayproductcode[0];
												}
											}
											break;
										case 'europe':
											$arrayproductcode = explode( ',', get_option( 'cdi_o_settings_colissimo_EuropeProductCodes' ) );
											if ( $pickupLocationId ) {
												$productcode = $arrayproductcode[2];
											} else {
												if ( $array_for_carrier['signature'] == 'yes' ) {
													$productcode = $arrayproductcode[1];
												} else {
													$productcode = $arrayproductcode[0];
												}
											}
											$insurancevalue = 0;
											break;
										case 'international':
											$arrayproductcode = explode( ',', get_option( 'cdi_o_settings_colissimo_InternationalProductCodes' ) );
											if ( $pickupLocationId ) {
												$productcode = $arrayproductcode[2];
											} else {
												if ( $array_for_carrier['signature'] == 'yes' ) {
													$productcode = $arrayproductcode[1];
												} else {
													$productcode = $arrayproductcode[0];
												}
											}
											break;
									} // End switch
								}
								// process of exception product codes
								$arrayexceptionproductcode = explode( ',', get_option( 'cdi_o_settings_colissimo_ExceptionProductCodes' ) );
								foreach ( $arrayexceptionproductcode as $exceptionproductcode ) {
									$arraytoreplace = explode( '=', $exceptionproductcode );
									$arraytoreplace = array_map( 'trim', $arraytoreplace );
									if ( $productcode == $arraytoreplace[0] ) {
										$productcode = $arraytoreplace[1];
										break;
									}
								}
								// ********************************* End Compute $parameters *********************************							
																
								$params = array();
								 
								$params['contractNumber'] = get_option( 'cdi_o_settings_colissimo_contractnumber' ) ;
								$params['password'] = get_option( 'cdi_o_settings_colissimo_password' ) ;
								
									$outputFormat = array () ;
									$outputFormat['x'] = get_option( 'cdi_o_settings_colissimo_X' ) ;
									$outputFormat['y'] = get_option( 'cdi_o_settings_colissimo_Y' ) ;
									$outputFormat['outputPrintingType'] = get_option( 'cdi_o_settings_colissimo_OutputPrintingType' ) ;																																	
								$params['outputFormat'] = $outputFormat ;
								
									$letter = array () ;
																	
										$service = array () ;
										$service['productCode'] = $productcode ;
										$calc = get_option( 'cdi_o_settings_colissimo_OffsetDepositDate' );
										$service['depositDate'] = date( 'Y-m-d', strtotime( "+$calc day" ) ) ;
										$num = $array_for_carrier['cn23_shipping']; // the data required by cn23 is shipping cost		
										$service['totalAmount'] = (float)(str_replace (',', '.', $num)) * 100 ;
										$service['orderNumber'] = $array_for_carrier['sender_parcel_ref'] ;
										$service['commercialName'] = get_option( 'cdi_o_settings_merchant_CompanyName' ) ;
										$ReturnTypeChoice = str_replace( array( 'no-return', 'pay-for-return' ), array( '2', '3' ), $array_for_carrier['return_type'] );
										if ( ! $ReturnTypeChoice ) {
											$ReturnTypeChoice = '2'; // fallback to be accepted by Colissimo
										}
										$service['returnTypeChoice'] = $ReturnTypeChoice ;									
									$letter['service'] = $service ;
															
										$parcel = array () ;				
										$parcel['insuranceValue'] = (float) $insurancevalue ;											
										$weight = (float)($array_for_carrier['parcel_weight']) / 1000;										
										$parcel['weight'] = $weight ;
										$NonMachinable = str_replace( array( 'colis-standard', 'colis-volumineux', 'colis-rouleau' ), array( '0', '1', '1' ), $array_for_carrier['parcel_type'] );							
										$parcel['nonMachinable'] = $NonMachinable ;	
										$parcel['instructions'] = $array_for_carrier['carrier_instructions'] ;
										$ReturnReceipt = str_replace( array( 'non', 'oui' ), array( '0', '1' ), $array_for_carrier['returnReceipt'] );
										if ( ! $ReturnReceipt ) {
											$ReturnReceipt = '0';
										}																			
										$parcel['returnReceipt'] = $ReturnReceipt ;
										$parcel['pickupLocationId'] = $pickupLocationId ;
										$Ftd = str_replace( array( 'non', 'oui' ), array( '0', '1' ), $array_for_carrier['ftd'] );
										if ( ! $Ftd ) {
											$Ftd = '0';
										}
										$parcel['ftd'] = $Ftd ;
										$parcel['ddp'] = '0' ; // Entry not already set								
									$letter['parcel'] = $parcel ;

										$customsDeclarations = array () ;
										$customsDeclarations['includeCustomsDeclarations'] = str_replace( array( 'no', 'yes' ), array( '0', '1' ), get_option( 'cdi_o_settings_colissimo_IncludeCustomsDeclarations' ) ) ;	
											$contents = array () ;

								// Add cn23 articles 0 to 99 if exist
								for ( $nbart = 0; $nbart <= 99; $nbart++ ) {
									if ( ! isset( $array_for_carrier[ 'cn23_article_description_' . $nbart ] ) ) {
										break;
									}
									$artweight = $array_for_carrier[ 'cn23_article_weight_' . $nbart ];
									if ( $artweight and is_numeric( $artweight ) and $artweight !== 0 ) { // Supp Virtual and no-weighted products
										$art = array ();
										$art['description'] = $array_for_carrier[ 'cn23_article_description_' . $nbart ] ;
										$art['quantity'] = $array_for_carrier[ 'cn23_article_quantity_' . $nbart ] ;										
										$art['weight'] = (float)$array_for_carrier[ 'cn23_article_weight_' . $nbart ] / 1000 ;
										$num = floor(str_replace (',', '.', $array_for_carrier[ 'cn23_article_value_' . $nbart ])) ;
											if ($num == 0) {
												$num = 1 ;
											}																			
										$art['value'] = $num ;										
										$art['hsCode'] = $array_for_carrier[ 'cn23_article_hstariffnumber_' . $nbart ] ;										
										$art['originCountry'] = $array_for_carrier[ 'cn23_article_origincountry_' . $nbart ] ;
										// Dupplicate keys not possible with PHP, so index neeeded but will be suppress when xml
										$contents['article-' . $nbart] = $art;
									}
								}
								
												$category = array () ;
												$category['value'] = $array_for_carrier['cn23_category'] ;
											$contents['category'] = $category ;								
									
										$customsDeclarations['contents'] = $contents ;														
									$letter['customsDeclarations'] = $customsDeclarations ;	
																	
										$sender = array () ;										
										$sender['senderParcelRef'] = $array_for_carrier['sender_parcel_ref'] ;
											$address = array () ;							
											$address['companyName'] = get_option( 'cdi_o_settings_merchant_CompanyName' ) ;
											$address['line1'] = get_option( 'cdi_o_settings_merchant_Line2' ) ;
											$address['line2'] = get_option( 'cdi_o_settings_merchant_Line1' ) ;
											$address['countryCode'] = get_option( 'cdi_o_settings_merchant_CountryCode' ) ;												
											$address['city'] = get_option( 'cdi_o_settings_merchant_City' ) ;												
											$address['zipCode'] = get_option( 'cdi_o_settings_merchant_ZipCode' ) ;	
											$address['email'] = get_option( 'cdi_o_settings_merchant_Email' ) ;												
										$sender['address'] = $address ;
									$letter['sender'] = $sender ;
																	
										$addressee = array () ;
											$address = array () ;																									
											if ( 'yes' == get_option( 'cdi_o_settings_companyandorderref' ) ) {
												$comp = $array_for_carrier['shipping_company'] . ' -' . $array_for_carrier['sender_parcel_ref'] . '-';
											} else {
												$comp = $array_for_carrier['shipping_company'];
											}
											$comp = apply_filters( 'cdi_filterstring_gateway_companyandorderid', $comp, $array_for_carrier );
											$address['companyName'] = $comp ;
											$address['lastName'] = $array_for_carrier['shipping_last_name'] ;
											$address['firstName'] = $array_for_carrier['shipping_first_name'] ;
											$address['line0'] = $array_for_carrier['shipping_address_3'] ;
											$address['line1'] = $array_for_carrier['shipping_address_2'] ;
											$address['line2'] = $array_for_carrier['shipping_address_1'] ;
											$address['line3'] = $array_for_carrier['shipping_address_4'] ;
											$address['countryCode'] = $array_for_carrier['shipping_country'] ;											
											$address['city'] = $array_for_carrier['shipping_city_state'] ;											
											$address['zipCode'] = $array_for_carrier['shipping_postcode'] ;	
											$PhoneNumber = cdi_c_Function::cdi_sanitize_phone( $array_for_carrier['billing_phone'] );											
											$address['phoneNumber'] = $PhoneNumber ;
											$MobileNumber = cdi_c_Function::cdi_sanitize_mobilenumber( $array_for_carrier['billing_phone'], $array_for_carrier['shipping_country'] );
											$MobileNumber = apply_filters( 'cdi_filterstring_auto_mobilenumber', $MobileNumber, $order_id );
											if ( isset( $MobileNumber ) && $MobileNumber !== '' ) {
												$address['mobileNumber'] = $MobileNumber ; // Set only if it is a mobile
											}			
											$address['email'] = $array_for_carrier['billing_email'] ;
										$addressee['address'] = $address ;
									$letter['addressee'] = $addressee ;										
								$params['letter'] = $letter ;
								
								// include special fields if any
								$eoricode = get_option( 'cdi_o_settings_cn23_eori' );
								if ( $eoricode ) {;
									$params['fields']['field'] = array ( 'key' => 'EORI', 'value' => $eoricode) ;
								}				

								//function convert array to xml
								$requestSoap = cdi_c_Function::generateValidXmlFromMixiedObj($params, 'CDI', array(), null) ;

								// Remove index in duplicate <article> coming from php
								$requestSoap = preg_replace( '/article-[0-9]+>/', 'article>', $requestSoap);

								// Set Headers
								$requestSoap = str_replace( '<CDI>', '<?xml version="1.0"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://sls.ws.coliposte.fr" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><SOAP-ENV:Body><ns1:generateLabel><generateLabelRequest>', $requestSoap);
								$requestSoap = str_replace( '</CDI>', '</generateLabelRequest></ns1:generateLabel></SOAP-ENV:Body></SOAP-ENV:Envelope>', $requestSoap);

								// ******* Execute Web service Colissimo *********************************************
								$clientSoap = new SoapClient(SERVER_NAME . '/sls-ws/SlsServiceWS/2.0?wsdl', array('cache_wsdl' => WSDL_CACHE_NONE) ) ;
								//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $clientSoap, 'exp');								
								$response = $clientSoap->__doRequest($requestSoap, SERVER_NAME . '/sls-ws/SlsServiceWS/2.0', '', 1);
								//$response = '--uuid:67ef9219-5356-4a6b-9372-fdcf781f5344 Content-Type: application/xop+xml; charset=UTF-8; type="text/xml"; Content-Transfer-Encoding: binary Content-ID: <root.message@cxf.apache.org> <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> <soap:Body> <ns2:generateLabelV2Response xmlns:ns2="http://sls.ws.coliposte.fr"> <return> <messages> <id>0</id> <messageContent>La requête a été traitée avec succès</messageContent> <type>INFOS</type> </messages> <labelV2Response> <label> <xop:Include href="cid:5592d0a6-7526-41a0-a186-45404fb52b6c- 59@cxf.apache.org" xmlns:xop="http://www.w3.org/2004/08/xop/include"/> </label> <cn23> <xop:Include href="cid:5592d0a6-7526-41a0-a186-45404fb52b6c- 60@cxf.apache.org" xmlns:xop="http://www.w3.org/2004/08/xop/include"/> </cn23> <parcelNumber>7Q05592274242</parcelNumber> <parcelNumberPartner>internat92274242</parcelNumberPartner>   <pdfUrl>https://pfi.telintrans.fr/sls- ws/GetLabel?parcelNumber=7Q05592274242&amp;signature=d0fe8cc2e3d35febd858b2f73b6a26cc4 edb8674820a7c4033982c08ad668374&amp;includeCustomsDeclarations=true</pdfUrl> </labelV2Response> </return> </ns2:generateLabelV2Response> </soap:Body> </soap:Envelope> --uuid:67ef9219-5356-4a6b-9372-fdcf781f5344--' ;

								$total_response = $response ;

								// Extract Label and cn23 and clean response with only XML
								$arrresult = cdi_Gateway_Colissimo_Affranchissement_fonct::store_attach_label_cn23_bordereau( $response );
								$base64label = $arrresult['base64label'] ;
								$base64cn23 = $arrresult['base64cn23'] ;
								$response = cdi_Gateway_Colissimo_Affranchissement_fonct::Keep_only_xml( $response );			

								$last = $requestSoap ;
								//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $last, 'exp');
								$ret = $response ;
								//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $ret, 'exp');
								
								$ok = cdi_c_Function::get_string_between( $response, '<return>', '</return>') ;
								$nok = cdi_c_Function::get_string_between( $response, '<soap:Fault>', '</soap:Fault>') ;
								
								if ($ok) {
									// Request accepted by server
									$ok = '<ok><return>' . $ok . '</return></ok>' ;
									//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $ok, 'exp');
									$return =  simplexml_load_string ($ok ) ;														 
									$retid = (string) $return->return->messages[0]->id;
									$retmessageContent = ( string) $return->return->messages[0]->messageContent;
									if ( $retid == 0 ) {
										// process the data
										$retparcelnumber = ( string) $return->return->labelV2Response->parcelNumber;
										$parcelNumberPartner = ( string) $return->return->labelV2Response->parcelNumberPartner;
										$retpdfurl = ( string) $return->return->labelV2Response->pdfUrl;
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Order : ' . $order_id . ' Parcel : ' . $retparcelnumber, 'msg' );
										$cdi_nbrwscorrect = $cdi_nbrwscorrect + 1;
										cdi_c_Function::cdi_stat( 'COL-aff' );
										$x = $wpdb->update(
											$wpdb->prefix . 'cdi',
											array(
												'cdi_tracking' => $retparcelnumber,
												'cdi_parcelNumberPartner' => $parcelNumberPartner,
												'cdi_hreflabel' => $retpdfurl,
											),
											array( 'cdi_order_id' => $order_id )
										);
										if ( $base64label ) {
											cdi_c_Function::cdi_uploads_put_contents( $order_id, 'label', $base64label );
										}else{
											cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'error - label missing', 'tec' );
										}
										if ( $base64cn23 ) {
											cdi_c_Function::cdi_uploads_put_contents( $order_id, 'cn23', $base64cn23 );
										}
										cdi_c_Gateway::cdi_synchro_gateway_to_order( $order_id );
									} else {
										// process the error from soap server
										cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $total_response, 'exp');
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retid, 'exp' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retmessageContent, 'exp' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__,  cdi_c_Function::prettyxml($last), 'tec' );
										cdi_c_Function::cdi_debug( __LINE__, __FILE__, $ret, 'exp' );
										$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $array_for_carrier['order_id'] . ' - ' . $retid . ' : ' . $retmessageContent;
									}
								} else {
									// process the error from soap client
									$nok = '<nok><fault>' . $nok . '</fault></nok>' ;
									//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $nok, 'exp');									
									$nok =  simplexml_load_string ($nok ) ;		
									$retid = $nok->fault->faultcode;
									$retmessageContent = $nok->fault->faultstring;
									cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $total_response, 'exp');
									cdi_c_Function::cdi_debug( __LINE__, __FILE__, (string) $retid, 'tec' );
									cdi_c_Function::cdi_debug( __LINE__, __FILE__, (string) $retmessageContent, 'tec' );
									cdi_c_Function::cdi_debug( __LINE__, __FILE__,  cdi_c_Function::prettyxml($last), 'tec' );
									cdi_c_Function::cdi_debug( __LINE__, __FILE__, $ret, 'tec' );
									$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $array_for_carrier['order_id'] . ' - ' . $retid . ' : ' . $retmessageContent;
								}
								// ********************************* End Colissimo Web service *********************************
							} // End !$cdi_tracking
							if ( $errorws !== null ) {
								break;
							}
						} // End row
						
						} // End test Preload OK

						// Close sequence
						$message = number_format_i18n( $cdi_nbrwscorrect ) . __( ' parcels processed with Colissimo Web Service.', 'cdi' ) . ' ' . $errorws;
						update_option( 'cdi_o_notice_display', $message );
						$sendback = admin_url() . 'admin.php?page=passerelle-cdi';
						wp_redirect( $sendback );
						exit();
						
						
						
					} // End cdi_nbrorderstodo
				} //End $results
			} // End current_user_can
		} // End cdi_Colissimo_run_affranchissement
	} // cdi_gateway_colissimo
} // End class

class cdi_Gateway_Colissimo_Affranchissement_fonct {
	public static function store_attach_label_cn23_bordereau( $response ) {
		// If exist, parse $response to extract and store label and cn23 and bordereau (in base64 format). Not use when Retour service
		$base64label = null;
		$base64cn23 = null;
		$base64bordereau = null;
		$return = array('base64label' => null, 'base64cn23' => null, 'base64bordereau' => null ) ;
		$response = str_replace( 'href="cid:', 'href="', $response ); // To avoid too many combinations
		$response = str_replace( 'Content-ID: <cid:', 'Content-ID: <', $response );
		$uuid = '--uuid' . cdi_c_Function::sup_line( cdi_c_Function::get_string_between( $response, '--uuid', 'Content-Type:' ) );
		$uuid = sanitize_text_field( $uuid );
		$xoplabel = cdi_c_Function::sup_line( cdi_c_Function::get_string_between( $response, '<label>', '</label>' ) );
		$hreflabel = cdi_c_Function::sup_line( cdi_c_Function::get_string_between( $xoplabel, 'href="', '"' ) );
		if ( $hreflabel ) {
			$base64labelA = cdi_c_Function::get_string_between( $response, 'Content-ID: <' . $hreflabel . '>', $uuid );
			$base64label = 'JVBER' . cdi_c_Function::get_string_between( $base64labelA, 'JVBER', null );
			if ( $base64label == 'JVBER' ) { // Empty, so seem being not in base64 but in direct stream
				$base64label = '%PDF' . cdi_c_Function::get_string_between( $base64labelA, '%PDF', '%%EOF' ) . '%%EOF';
				$base64label = base64_encode( $base64label );
			}
			$return['base64label'] = $base64label ;
			if (!$base64label ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, '*** error href label announced but not present : ' . $hreflabel , 'exp' );
			}
		}
		$xopcn23 = cdi_c_Function::sup_line( cdi_c_Function::get_string_between( $response, '<cn23>', '</cn23>' ) );
		$hrefcn23 = cdi_c_Function::sup_line( cdi_c_Function::get_string_between( $xopcn23, 'href="', '"' ) );
		if ( $hrefcn23 ) {
			$base64cn23A = cdi_c_Function::get_string_between( $response, 'Content-ID: <' . $hrefcn23 . '>', $uuid );
			$base64cn23 = 'JVBER' . cdi_c_Function::get_string_between( $base64cn23A, 'JVBER', null );
			if ( $base64cn23 == 'JVBER' ) { // Empty, so seem being not in base64 but in direct stream
				$base64cn23 = '%PDF' . cdi_c_Function::get_string_between( $base64cn23A, '%PDF', '%%EOF' ) . '%%EOF';
				$base64cn23 = base64_encode( $base64cn23 );
			}
			$return['base64cn23'] = $base64cn23 ;
			if (!$base64cn23 ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, '*** error href cn23 announced but not present : ' . $hrefcn23 , 'exp' );
			}			
		}
		$xopbordereau = cdi_c_Function::sup_line( cdi_c_Function::get_string_between( $response, '<bordereauDataHandler>', '</bordereauDataHandler>' ) );
		$hrefbordereau = cdi_c_Function::sup_line( cdi_c_Function::get_string_between( $xopbordereau, 'href="', '"' ) );
		if ( $hrefbordereau ) {
			$base64bordereauA = cdi_c_Function::get_string_between( $response, 'Content-ID: <' . $hrefbordereau . '>', $uuid );
			$base64bordereau = 'JVBER' . cdi_c_Function::get_string_between( $base64bordereauA, 'JVBER', null );
			if ( $base64bordereau == 'JVBER' ) { // Empty, so seem being not in base64 but in direct stream
				$base64bordereau = '%PDF' . cdi_c_Function::get_string_between( $base64bordereauA, '%PDF', '%%EOF' ) . '%%EOF';
				$base64bordereau = base64_encode( $base64bordereau );
			}
			$return['base64bordereau'] = $base64bordereau ;
			if (!$base64bordereau ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, '*** error href bordereau announced but not present : ' . $hrefbordereau , 'exp' );
			}
		}
		return $return ;
	}

	public static function Keep_only_xml( $response ) {
		// if response content type is Mtom, strip away everything but the xml
		if ( strpos( $response, 'Content-Type: application/xop+xml' ) !== false ) {
			// Keep only soap Envelope
			$tempstr = stristr( $response, '<soap:Envelope' );
			$response = substr( $tempstr, 0, strpos( $tempstr, '/soap:Envelope>' ) ) . '/soap:Envelope>';
			// If exist remove xop part inside <label>
			$tempstr = stristr( $response, '<label>' );
			$suppress = substr( $tempstr, 0, strpos( $tempstr, '</label>' ) ) . '</label>';
			$response = str_replace( $suppress, '', $response );
			// If exist remove xop part inside <cn23>
			$tempstr = stristr( $response, '<cn23>' );
			$suppress = substr( $tempstr, 0, strpos( $tempstr, '</cn23>' ) ) . '</cn23>';
			$response = str_replace( $suppress, '', $response );
			// If exist remove xop part inside <bordereauDataHandler>
			$tempstr = stristr( $response, '<bordereauDataHandler>' );
			$suppress = substr( $tempstr, 0, strpos( $tempstr, '</bordereauDataHandler>' ) ) . '</bordereauDataHandler>';
			$response = str_replace( $suppress, '', $response );
		}
		$response = str_replace( array( "\r\n", "\r", "\n" ), '', $response );
		$response = str_replace( '  ', ' ', $response );
		return $response;
	}
}



