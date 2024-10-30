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
/* Colissimo Retour Colis                                                               */
/****************************************************************************************/

class cdi_c_Colissimo_Retourcolis {
	public static function init() {
	}

	public static function cdi_colissimo_calc_parcelretour( $order_id, $productcode ) {
		global $woocommerce;

		// ********************************* Begin Colissimo Web service *********************************
		// Document Technique - Version Juillet 2022 - Spécifications du Web Service d’Affranchissement Colissimo
		// Direct use of SOAP, use of WS Colissimo 2.0, https exchanges, SOAP at 1.0
		
		$errorws = null;
		$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );

		define("SERVER_NAME", 'https://ws.colissimo.fr'); 
																
		$params = array();
								 
		$params['contractNumber'] = get_option( 'cdi_o_settings_colissimo_contractnumber' ) ;
		$params['password'] = get_option( 'cdi_o_settings_colissimo_password' ) ;
								
			$outputFormat = array () ;
			$outputFormat['x'] = get_option( 'cdi_o_settings_colissimo_X' ) ;
		$outputFormat['y'] = get_option( 'cdi_o_settings_colissimo_Y' ) ;
		$outputFormat['outputPrintingType'] = 'PDF_A4_300dpi' ; // Forced to A4 pdf because generally consumer has this printer																																
		$params['outputFormat'] = $outputFormat ;
		
			$letter = array () ;
																	
				$service = array () ;
				$service['productCode'] = $productcode ; // Only France zone may be in scope. Waiting for Colissimo explanations
				$calc = get_option( 'cdi_o_settings_colissimo_OffsetDepositDate' );
				$service['depositDate'] = date( 'Y-m-d', strtotime( "+$calc day" ) ) ;
				$service['orderNumber'] = $array_for_carrier['sender_parcel_ref'] ;
				$service['commercialName'] = get_option( 'cdi_o_settings_merchant_CompanyName' ) ;
			$letter['service'] = $service ;

				$parcel = array () ;				
				$parcel['insuranceValue'] = '0' ;											
				$weight = (float)($array_for_carrier['parcel_weight']) / 1000;										
				$parcel['weight'] = $weight ;
				$NonMachinable = str_replace( array( 'colis-standard', 'colis-volumineux', 'colis-rouleau' ), array( '0', '1', '1' ), $array_for_carrier['parcel_type'] );							
				$parcel['nonMachinable'] = $NonMachinable ;																
				$parcel['returnReceipt'] = '0' ;			
			$letter['parcel'] = $parcel ;

				$customsDeclarations = array () ;
				$customsDeclarations['includeCustomsDeclarations'] = 'false' ;	// Only return from France so no customsDeclarations
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
				$sender['senderParcelRef'] = $order_id . '-Return';
					$address = array () ;
					$companyandorderid = $array_for_carrier['shipping_company'] . ' -' . $array_for_carrier['order_id'] . '-';														
					$address['companyName'] = $companyandorderid ;
					$address['lastName'] = $array_for_carrier['shipping_last_name']  ;											
					$address['firstName'] = $array_for_carrier['shipping_first_name']  ;
					$address['line1'] = $array_for_carrier['shipping_address_2'] ;
					$address['line2'] = $array_for_carrier['shipping_address_1'] ;
					$address['countryCode'] = $array_for_carrier['shipping_country'] ;		
					$address['city'] = $array_for_carrier['shipping_city_state'] ;											
					$address['zipCode'] = $array_for_carrier['shipping_postcode']  ;										
					$PhoneNumber = cdi_c_Function::cdi_sanitize_phone( $array_for_carrier['billing_phone'] );																				
					$address['phoneNumber'] = $PhoneNumber ;																				
					$address['email'] = $array_for_carrier['billing_email'] ;												
				$sender['address'] = $address ;
			$letter['sender'] = $sender ;

				$addressee = array () ;
				$addressee['addresseeParcelRef'] = $order_id . '-Return' ;										
				$addressee['codeBarForReference'] = 'true' ;
				$addressee['serviceInfo'] = get_option( 'cdi_o_settings_colissimo_returnparcelservice' ) ;										
																				
					$address = array () ;																									
					$address['companyName'] = get_option( 'cdi_o_settings_merchant_CompanyName' ) ;
					$address['lastName'] = get_option( 'cdi_o_settings_merchant_CompanyName' ) ;
					$address['line1'] = get_option( 'cdi_o_settings_merchant_Line2' ) ;																							
					$address['line2'] = get_option( 'cdi_o_settings_merchant_Line1' ) ;																					
					$address['countryCode'] = get_option( 'cdi_o_settings_merchant_CountryCode' ) ;											
					$address['city'] = get_option( 'cdi_o_settings_merchant_City' ) ;											
					$address['zipCode'] = get_option( 'cdi_o_settings_merchant_ZipCode' ) ;										
					$address['email'] = get_option( 'cdi_o_settings_merchant_Email' ) ;
				$addressee['address'] = $address ;
			$letter['addressee'] = $addressee ;										
		$params['letter'] = $letter ;
								
		// include special fields if any
		$eoricode = get_option( 'cdi_o_settings_cn23_eori' );
		if ( $eoricode ) {;
			$params['fields']['field'] = array ( 'key' => 'EORI', 'value' => $eoricode) ;
		}				
																	
		//function convert array to xml
		$requestSoap = cdi_c_Function::generateValidXmlFromMixiedObj($params, 'CDI', array(), null) ;;		
					
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
																
		// Extract Label and cn23 and clean response with only XML
		$arrresult = cdi_Gateway_Colissimo_Affranchissement_fonct::store_attach_label_cn23_bordereau( $response );
		$base64label = $arrresult['base64label'] ;
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
				$order = wc_get_order( $order_id );
				$retparcelnumber = ( string) $return->return->labelV2Response->parcelNumber;
				$order->delete_meta_data( '_cdi_meta_parcelnumber_return' );
				$order->add_meta_data( '_cdi_meta_parcelnumber_return', $retparcelnumber );
				$parcelNumberPartner = ( string) $return->return->labelV2Response->parcelNumberPartner;
				$retpdfurl = ( string) $return->return->labelV2Response->pdfUrl;
				$order->delete_meta_data( '_cdi_meta_pdfurl_return' );
				$order->add_meta_data( '_cdi_meta_pdfurl_return', $retpdfurl );															
				$order->delete_meta_data( '_cdi_meta_return_executed' );
				$order->add_meta_data( '_cdi_meta_return_executed', 'yes' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Order : ' . $order_id . ' Parcel : ' . $retparcelnumber, 'msg' );
				if ( $base64label ) {
					$order->delete_meta_data( '_cdi_meta_base64_return' );
					$order->add_meta_data( '_cdi_meta_base64_return', $base64label );
				}
				$order->save();
				cdi_c_Function::cdi_stat( 'COL-ret' );			
			} else {
				// process the error from soap server
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retid, 'exp' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retmessageContent, 'exp' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, cdi_c_Function::prettyxml($last), 'exp' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $ret, 'exp' );
				$errorws = __( ' ===> Return label not available - #', 'cdi' ) . $order_id . ' - ' . $retid . ' : ' . $retmessageContent;
			}
		} else {
			// process the error from soap client
			$nok = '<nok><fault>' . $nok . '</fault></nok>' ;
			//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $nok, 'exp');									
			$nok =  simplexml_load_string ($nok ) ;		
			$retid = $nok->fault->faultcode;
			$retmessageContent = $nok->fault->faultstring;
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, (string) $retid, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, (string) $retmessageContent, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__,  cdi_c_Function::prettyxml($last), 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $ret, 'tec' );
			$errorws = __( ' ===> Return label not available - #', 'cdi' ) . $order_id . ' - ' . $retid . ' : ' . $retmessageContent;
		}
		// ********************************* End Colissimo Web service *********************************

		// Close sequence
		if ( null !== $errorws ) {
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $errorws, 'exp' );
			echo wp_kses_post( $errorws );
		}
	}
}




