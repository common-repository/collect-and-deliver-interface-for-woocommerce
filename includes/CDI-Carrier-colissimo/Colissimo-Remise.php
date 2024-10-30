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
/* Colissimo Bordereau de Remise (dépôt)                                                              */
/****************************************************************************************/

class cdi_c_Colissimo_Remise_bordereau {
	public static function init() {
	}

	public static function cdi_colissimo_prod_remise_bordereau( $selected ) {
		$errorws = '';
		// Compute packages size to do (La Poste has a 250 limit)
		$limit = 100;
		$nbpackage = intdiv( count( $selected ), $limit );
		$remainder = count( $selected ) % $limit;
		if ( $remainder != 0 ) {
			$nbpackage = $nbpackage + 1;
		}
		if ( $nbpackage !== 0 ) {
			$sizepackage = intdiv( count( $selected ), $nbpackage ) + 1;
		} else {
			$sizepackage = 0;
		}
		$chargement = array();
		$package = array();
		$countparcel = 0;
		foreach ( $selected as $parcel ) {
			$countparcel = $countparcel + 1;
			$package[] = $parcel;
			if ( $countparcel == $sizepackage ) {
				$chargement[] = $package;
				$package = array();
			}
		}
		if ( count( $package ) > 0 ) { // Last package
			$chargement[] = $package;
		}
		$rangchargement = 0;
		foreach ( $chargement as $package ) {
			$rangchargement = $rangchargement + 1;
			if ( ! empty( $package ) ) {
				sleep( 1 ); // To avoid trombosis in the LaPoste server if requests are chained
				
		
				define("SERVER_NAME", 'https://ws.colissimo.fr'); 
																
				$params = array();
								 
				$params['contractNumber'] = get_option( 'cdi_o_settings_colissimo_contractnumber' ) ;
				$params['password'] = get_option( 'cdi_o_settings_colissimo_password' ) ;		
					$generateBordereauParcelNumberList = array() ;;
					$i = 0 ;
					foreach ( $package as $parcelsnumbers ) {
						$i = $i + 1 ;
						$tracking = cdi_c_Function::get_string_between( $parcelsnumbers, '[', ']' );
						$generateBordereauParcelNumberList['parcelsNumbers-' . $i] = $tracking ;							
					}
				$params['generateBordereauParcelNumberList'] = $generateBordereauParcelNumberList ;
			
				//function convert array to xml
				$requestSoap = cdi_c_Function::generateValidXmlFromMixiedObj($params, 'CDI', array(), null) ;;

				// Remove index in duplicate <article> coming from php
				$requestSoap = preg_replace( '/parcelsNumbers-[0-9]+>/', 'parcelsNumbers>', $requestSoap);				

				// Set Headers
				$requestSoap = str_replace( '<CDI>', '<?xml version="1.0"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://sls.ws.coliposte.fr" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><SOAP-ENV:Body><ns1:generateBordereauByParcelsNumbers>', $requestSoap);
				$requestSoap = str_replace( '</CDI>', '</ns1:generateBordereauByParcelsNumbers></SOAP-ENV:Body></SOAP-ENV:Envelope>', $requestSoap);

				// ******* Execute Web service Colissimo *********************************************
				$clientSoap = new SoapClient(SERVER_NAME . '/sls-ws/SlsServiceWS?wsdl', array('cache_wsdl' => WSDL_CACHE_NONE) ) ;
				//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $clientSoap, 'exp');								
				$response = $clientSoap->__doRequest($requestSoap, SERVER_NAME . '/sls-ws/SlsServiceWS', '', 1);
				//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $response, 'exp');

				// If any extract bordereau and clean response with only XML
				$arrresult = cdi_Gateway_Colissimo_Affranchissement_fonct::store_attach_label_cn23_bordereau( $response );
				$base64bordereau = $arrresult['base64bordereau'] ;
	
				$response = cdi_Gateway_Colissimo_Affranchissement_fonct::Keep_only_xml( $response );	

				$last = $requestSoap ;
				$ret = $response ;
				$ok = cdi_c_Function::get_string_between( $response, '<return>', '</return>') ;
				$nok = cdi_c_Function::get_string_between( $response, '<soap:Fault>', '</soap:Fault>') ;
								
				if ($ok) {
					$ok = '<ok><return>' . $ok . '</return></ok>' ;
					//cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $ok, 'exp');
					$ok =  simplexml_load_string ($ok ) ;
					$retmessageContent = (string) $ok->return->messages[0]->messageContent;									
					$retid = (string) $ok->return->messages[0]->id;				
					if ( $retid == 0 ) {
						// process the data
                      				$address = (string) $ok->return->bordereau->bordereauHeader->address;
                      				$bordereauNumber = (string) $ok->return->bordereau->bordereauHeader->bordereauNumber;
                      				$clientNumber = (string) $ok->return->bordereau->bordereauHeader->clientNumber;
                      				$codeSitePCH = (string) $ok->return->bordereau->bordereauHeader->codeSitePCH;
                      				$company = (string) $ok->return->bordereau->bordereauHeader->company;
                      				$numberOfParcels = (string) $ok->return->bordereau->bordereauHeader->numberOfParcels;
                      				$publishingDate = (string) $ok->return->bordereau->bordereauHeader->publishingDate;
						$datetimebd = date( 'Y-m-d H:i:s' );
						if ( $base64bordereau ) {
							cdi_c_Gateway_Bordereaux::cdi_stockage_bordereau( 'BD-' . 'colissimo', $bordereauNumber . '-' . $rangchargement, '=> ' . $datetimebd, $numberOfParcels, $base64bordereau );
						} else {
							$errorws = __( ' ===> Error processing Bordereau de dépôt Colissimo', 'cdi' ) . ' - ' . $retid . ' : ' . $retmessageContent;
						}
					} else {
						// process the error from soap server
						cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retid, 'exp' );
						cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retmessageContent, 'exp' );
						cdi_c_Function::cdi_debug( __LINE__, __FILE__, $last, 'exp' );
						cdi_c_Function::cdi_debug( __LINE__, __FILE__, $ret, 'exp' );
						$errorws = __( ' ===> Error processing Bordereau de dépôt Colissimo', 'cdi' ) . ' - ' . $retid . ' : ' . $retmessageContent;
					}
				} else {
					// process the error from soap client			
				        $retid = cdi_c_Function::get_string_between( $response, '<faultcode>', '</faultcode>') ;
				        $retmessageContent = cdi_c_Function::get_string_between( $response, '<faultstring>', '</faultstring>') ;				
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retid, 'tec' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $retmessageContent, 'tec' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $last, 'tec' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $ret, 'tec' );
					$errorws = __( ' ===> Error processing Bordereau de dépôt Colissimo', 'cdi' ) . ' - ' . $retid . ' : ' . $retmessageContent;
				}
			} // End Empty package
			if ( $errorws !== '' ) {
				$message = $errorws;
				break;
			}
		} // End foreach $chargement
		if ( $errorws !== '' ) {
			$message = $errorws;
		} else {
			$message = $nbpackage . "bordereau(s) a(ont) été généré(s). Ce sont les premiers de votre liste. Cliquez sur \"Voir\" pour le visualiser ou l'imprimer";
		}
		return $message;
	}
}




