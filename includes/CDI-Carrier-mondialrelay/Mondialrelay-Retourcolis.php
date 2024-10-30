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
/* Mondial Relay Retour Colis                                                           */
/****************************************************************************************/

class cdi_c_Mondialrelay_Retourcolis {
	public static function init() {
	}

	public static function cdi_mondialrelay_calc_parcelretour( $order_id, $productcode ) {
		global $woocommerce;
		global $MRerrcodlist;
		$order = wc_get_order( $order_id );
		$errorws = null;
		// ********************************* Begin Mondial Relay Web service *********************************
		$array_for_carrier = apply_filters( 'cdi_filterarray_auto_arrayforcarrier', cdi_c_Function::cdi_array_for_carrier( $order_id ) );
		$order_id = $array_for_carrier['order_id'];

		$MR_WebSiteId = get_option( 'cdi_o_settings_mondialrelay_contractnumber' );
		$MR_WebSiteKey = get_option( 'cdi_o_settings_mondialrelay_password' );

		$client = new nusoap_client( 'https://api.mondialrelay.com/Web_Services.asmx?WSDL', true );
		$client->soap_defencoding = 'utf-8';

		$ModeCol = 'REL';
		$product_code = get_option( 'cdi_o_settings_mondialrelay_returndeliver' );
		$shipping_country = $array_for_carrier['shipping_country'];

		$Enseigne = $MR_WebSiteId;

		$ModeLiv = $product_code;
		$NDossier = $array_for_carrier['sender_parcel_ref'] . '-' . $array_for_carrier['ordernumber'];
		$NClient = $array_for_carrier['sender_parcel_ref'];
		$Expe_Langage = 'FR';

		$Expe_Ad1 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'] ) );
		$Expe_Ad2 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_address_2'] ) );
		$Expe_Ad3 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_address_1'] ) );
		$Expe_Ad4 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_address_3'] . ' ' . $array_for_carrier['shipping_address_4'] ) );
		$Expe_Ville = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( $array_for_carrier['shipping_city_state'] ) );
		$Expe_CP = $array_for_carrier['shipping_postcode'];
		$Expe_Pays = $array_for_carrier['shipping_country'];

		$PhoneNumber = cdi_c_Function::cdi_sanitize_phone( $array_for_carrier['billing_phone'] );
		$Expe_Tel1 = $PhoneNumber;
		$MobileNumber = cdi_c_Function::cdi_sanitize_mobilenumber( $array_for_carrier['billing_phone'], $array_for_carrier['shipping_country'] );
		$MobileNumber = apply_filters( 'cdi_filterstring_auto_mobilenumber', $MobileNumber, $order_id );
		if ( isset( $MobileNumber ) && $MobileNumber !== '' ) {
			$Expe_Tel2 = $MobileNumber; // Set only if it is a mobile
		} else {
			$Expe_Tel2 = '';
		}
		$Expe_Mail = $array_for_carrier['billing_email'];

		$Dest_Langage = 'FR';
		$Dest_Ad1 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_CompanyName' ) ) );
		$Dest_Ad2 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_mondialrelay_returnparcelservice' ) ) );
		$Dest_Ad3 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_Line1' ) ) );
		$Dest_Ad4 = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_Line2' ) ) );
		$Dest_Ville = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_accents( get_option( 'cdi_o_settings_merchant_City' ) ) );
		$Dest_CP = get_option( 'cdi_o_settings_merchant_ZipCode' );
		$Dest_Pays = get_option( 'cdi_o_settings_merchant_CountryCode' );

		$Dest_Tel1 = get_option( 'cdi_o_settings_merchant_fixphone' );
		$Dest_Tel2 = get_option( 'cdi_o_settings_merchant_cellularphone' );
		$Dest_Mail = get_option( 'cdi_o_settings_merchant_Email' );

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
		$LIV_Rel_Pays = '';
		$LIV_Rel = '';
		if ( $ModeLiv == '24R' ) {
			$ArrLivRel = explode( '-', get_option( 'cdi_o_settings_mondialrelay_returnrelayid' ) );
			$LIV_Rel_Pays = $ArrLivRel[0];
			$LIV_Rel = sprintf( '%06d', $ArrLivRel[1] );
		}
		$TAvisage = '';
		$TReprise = 'N';
		$Montage = 0;
		$TRDV = '';
		$Assurance = 0;
		$Instructions = '';

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
			'LIV_Rel' => $LIV_Rel,
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
		$addmerchanttext = apply_filters( 'cdi_filterstring_MR_addlabeltext', $addmerchanttext, 'retour', $order_id, $array_for_carrier );
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
			$errorws = __( ' ===> Return label not available - #', 'cdi' ) . $order_id . ' - Mondial Relay : Fault (Expect -The request contains an invalid SOAP body)';
		} else {
			$err = $client->getError();
			if ( $err ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $err, 'tec' );				
				$errorws = __( ' ===> Return label not available - #', 'cdi' ) . $order_id . ' - ' . $err ;
			} else {
				$errorid = $result['WSI2_CreationEtiquetteResult']['STAT'];
				if ( $errorid != 0 ) {
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
					$errorws = __( ' ===> Return label not available - #', 'cdi' ) . $order_id . ' - ' . $errorid . ' : ' . $MRerrcodlist[ $errorid ];
				} else {
					// process the data
					$retparcelnumber = $result['WSI2_CreationEtiquetteResult']['ExpeditionNum'];
					$order->delete_meta_data( '_cdi_meta_parcelnumber_return' );
					$order->add_meta_data( '_cdi_meta_parcelnumber_return', $retparcelnumber );
					$retpdfurl = 'https://www.mondialrelay.com' . $result['WSI2_CreationEtiquetteResult']['URL_Etiquette'];
					$order->delete_meta_data( '_cdi_meta_pdfurl_return' );
					$order->add_meta_data( '_cdi_meta_pdfurl_return', $retpdfurl );
					$order->delete_meta_data( '_cdi_meta_return_executed' );
					$order->add_meta_data( '_cdi_meta_return_executed', 'yes' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Order : ' . $order_id . ' Parcel : ' . $retparcelnumber, 'msg' );

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
						$pdf->Text( $x, $y + 0, utf8_decode( "L'étiquette retour de votre commande " . $NDossier ) );
						$pdf->Text( $x, $y + 5, utf8_decode( 'est disponible en cliquant sur le lien ci-dessous. ' ) );
						// link
						$x = 5;
						$y = 50;  // x=col y=line
						$pdf->SetXY( $x, $y );
						$pdf->SetTextColor( 0, 0, 255 );
						$pdf->SetFont( 'Arial', 'B', 12 );
						$pdf->Text( $x + 20, $y + 5, utf8_decode( 'Etiquette ' . $retparcelnumber ) );
						$pdf->Link( $x, $y, 90, 10, $retpdfurl );
						$label = $pdf->Output( 'S' );
					}

					$base64labelreturn = base64_encode( $label );
					if ( $base64labelreturn ) {
						$order->delete_meta_data( '_cdi_meta_base64_return' );
						$order->add_meta_data( '_cdi_meta_base64_return', $base64labelreturn );
					}
					if ( get_option( 'cdi_o_settings_mondialrelay_contractnumber' ) !== 'BDTEST13' ) {
						cdi_c_Function::cdi_stat( 'MON-ret' );
					} else {
						cdi_c_Function::cdi_stat( 'MON-ret-test' );
					}
					$order->save();					
				}
			}
		}

		// ********************************* End Mondial Relay Web service *********************************
		// Close sequence
		if ( null !== $errorws ) {
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $errorws, 'exp' );
			echo wp_kses_post( $errorws );
		}
	}
}




