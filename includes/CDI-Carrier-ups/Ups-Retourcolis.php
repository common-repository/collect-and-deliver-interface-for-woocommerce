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
/* UPS Retour Colis                                                               */
/****************************************************************************************/

class cdi_c_Ups_Retourcolis {
	public static function init() {
	}

	public static function cdi_ups_calc_parcelretour( $order_id, $productcode ) {
		global $woocommerce;
		$order = wc_get_order( $order_id );
		$errorws = null;
		$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );

		include_once( 'ups-access-context.php' );

		// ****** Custom for some datas
		$codeproduct = '11'; // Always return with UPS Standart
		$shipping_country = $array_for_carrier['shipping_country'];
		$cdireference = $array_for_carrier['sender_parcel_ref'] . '(' . $array_for_carrier['ordernumber'] . ')';
		$detailshipment = get_option( 'cdi_o_settings_global_shipment_description' );

		$shipperphone = $array_for_carrier['billing_phone'];
		$shippercellular = cdi_c_Function::cdi_sanitize_mobilenumber( $array_for_carrier['billing_phone'], $array_for_carrier['shipping_country'] );
		if ( $shippercellular ) {
			$shipperphone = $shippercellular;
		}
		$shipperphone = apply_filters( 'cdi_filterstring_auto_mobilenumber', $shipperphone, $order_id );

		// Only one phone in UPS for fix and cellular. For merchant and for customer. So the cellular is the option
		$shippermerchant = get_option( 'cdi_o_settings_merchant_fixphone' );
		if ( get_option( 'cdi_o_settings_merchant_cellularphone' ) != '' ) {
			$shippermerchant = get_option( 'cdi_o_settings_merchant_cellularphone' );
		}

		// ****** Shipment Request
		$headers = null ;								
		$token = cdi_c_Ups_Get_Oauthcode::cdi_Ups_OAuth_get_token() ;
		if ($token) {
			//set headers with token
			$headers = [
				'Authorization: Bearer ' . $token,				    	
				'Content-Type: application/json',									
				'transId: CDI-' . $order_id . '-' . time(),
				'transactionSrc: CDI-Wordpress-WooCommerce',  
				];
		}
		
		// Create ShipmentConfirmRequest XMl
		$shipmentConfirmRequestXML = new SimpleXMLElement( '<ShipmentConfirmRequest ></ShipmentConfirmRequest>' );
		$request = $shipmentConfirmRequestXML->addChild( 'Request' );
		$request->addChild( 'SubVersion', '1801' );
		$request->addChild( 'RequestOption', 'nonvalidate' );
		$transactionReference = $request->addChild( 'TransactionReference' );
		$transactionReference->addChild( 'CustomerContext', $cdireference );
		
		// Shipment
		$shipment = $shipmentConfirmRequestXML->addChild( 'Shipment' );		
		$shipment->addChild( 'Description', 'Return : ' . $detailshipment );
		
		// Return
		$returnService = $shipment->addChild( 'ReturnService' );
		$returnService->addChild( 'Code', '9' );
		$returnService->addChild( 'Description', 'Return : ' . $detailshipment );
		
		// Shipper
		$shipper = $shipment->addChild( 'Shipper' );
		$shipper->addChild( 'Name', get_option( 'cdi_o_settings_merchant_CompanyName' ) );
		$shipper->addChild( 'AttentionName', get_option( 'cdi_o_settings_merchant_CompanyName' ) );
		$shipper->addChild( 'ShipperNumber', $upscomptenumber );
		// $shipper->addChild ( "TaxIdentificationNumber", $TaxIdentificationNumber );
		$shipper->addChild( 'PhoneNumber', $shippermerchant );
		$shipper->addChild( 'EMailAddress', get_option( 'cdi_o_settings_merchant_Email' ) );
		  $shipperAddress = $shipper->addChild( 'Address' );
		  $shipperAddress->addChild( 'AddressLine1', get_option( 'cdi_o_settings_merchant_Line1' ) );
		  $shipperAddress->addChild( 'AddressLine2', get_option( 'cdi_o_settings_merchant_Line2' ) );
		  $shipperAddress->addChild( 'AddressLine3', get_option( 'cdi_o_settings_ups_returnparcelservice' ) );
		  $shipperAddress->addChild( 'City', get_option( 'cdi_o_settings_merchant_City' ) );
		  $shipperAddress->addChild( 'StateProvinceCode', '' );
		  $shipperAddress->addChild( 'PostalCode', get_option( 'cdi_o_settings_merchant_ZipCode' ) );
		  $shipperAddress->addChild( 'CountryCode', get_option( 'cdi_o_settings_merchant_CountryCode' ) );		
		
		// Shipto
		$shipTo = $shipment->addChild( 'ShipTo' );
		$shipTo->addChild( 'Name', get_option( 'cdi_o_settings_merchant_CompanyName' ) );
		$shipTo->addChild( 'AttentionName', get_option( 'cdi_o_settings_merchant_CompanyName' ) );
		$shipTo->addChild( 'PhoneNumber', $shippermerchant );
		$shipTo->addChild( 'EMailAddress', get_option( 'cdi_o_settings_merchant_Email' ) );
		  $shipToAddress = $shipTo->addChild( 'Address' );
		  $shipToAddress->addChild( 'AddressLine1', get_option( 'cdi_o_settings_merchant_Line1' ) );
		  $shipToAddress->addChild( 'AddressLine2', get_option( 'cdi_o_settings_merchant_Line2' ) );
		  $shipToAddress->addChild( 'AddressLine3', get_option( 'cdi_o_settings_ups_returnparcelservice' ) );
		  $shipToAddress->addChild( 'City', get_option( 'cdi_o_settings_merchant_City' ) );
		  $shipToAddress->addChild( 'StateProvinceCode', '' );
		  $shipToAddress->addChild( 'PostalCode', get_option( 'cdi_o_settings_merchant_ZipCode' ) );
		  $shipToAddress->addChild( 'CountryCode', get_option( 'cdi_o_settings_merchant_CountryCode' ) );
		  
		// Shipfrom
		$shipFrom = $shipment->addChild( 'ShipFrom' );
		$shipFrom->addChild( 'Name', $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'] );
		$shipFrom->addChild( 'AttentionName', $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'] );
		$shipFrom->addChild( 'PhoneNumber', $shipperphone );
		  $shipFromAddress = $shipFrom->addChild( 'Address' );
		  $shipFromAddress->addChild( 'AddressLine1', $array_for_carrier['shipping_address_1'] );
		  $shipFromAddress->addChild( 'AddressLine2', $array_for_carrier['shipping_address_2'] );
		  $shipFromAddress->addChild( 'AddressLine3', $array_for_carrier['shipping_address_3'] . ' ' . $array_for_carrier['shipping_address_4'] );
		  $shipFromAddress->addChild( 'City', $array_for_carrier['shipping_city'] );
		  $shipFromAddress->addChild( 'StateProvinceCode', $array_for_carrier['shipping_state'] );
		  $shipFromAddress->addChild( 'PostalCode', $array_for_carrier['shipping_postcode'] );
		  $shipFromAddress->addChild( 'CountryCode', $array_for_carrier['shipping_country'] );		
		  
		// Payment
		$paymentInformation = $shipment->addChild( 'PaymentInformation' );
		  $prepaid = $paymentInformation->addChild( 'Prepaid' );
			$billShipper = $prepaid->addChild( 'BillShipper' );
			$billShipper->addChild( 'AccountNumber', $upscomptenumber );

		// Reference
		$referenceNumber = $shipment->addChild( 'ReferenceNumber' );
		$referenceNumber->addChild( 'Code', 'CD' );
		$referenceNumber->addChild( 'Value', 'R - ' . get_option( 'cdi_installation_id' ) . ' - ' . $cdireference );		  
		  
		// Service
		$service = $shipment->addChild( 'Service' );
		$service->addChild( 'Code', $codeproduct );
		$service->addChild( 'Description', '' );
		
		// Package
		$package = $shipment->addChild( 'Package' );
		$package->addChild( 'Description', 'Return to marchand : ' );
		  $packagingType = $package->addChild( 'PackagingType' );
		  $packagingType->addChild( 'Code', '02' );
		  $packagingType->addChild( 'Description', 'Customer Supplied Package' );
		  $packageWeight = $package->addChild( 'PackageWeight' );
			$unitOfMeasurement = $packageWeight->addChild( 'UnitOfMeasurement' );
			$unitOfMeasurement->addChild( 'code', 'KGS' );
		  $packageWeight->addChild( 'Weight', (float)($array_for_carrier['parcel_weight']) / 1000 );		
		
		// Label
		$labelSpecification = $shipmentConfirmRequestXML->addChild( 'LabelSpecification' );
		$labelSpecification->addChild( 'HTTPUserAgent', '' );
		  $labelPrintMethod = $labelSpecification->addChild( 'LabelPrintMethod' );
		  $labelPrintMethod->addChild( 'Code', 'GIF' );
		  $labelPrintMethod->addChild( 'Description', '' );
			$labelImageFormat = $labelSpecification->addChild( 'LabelImageFormat' );
			$labelImageFormat->addChild( 'Code', 'GIF' );
			$labelImageFormat->addChild( 'Description', '' );		
		
		// Convert XML to RESTful (array)
		$requestXML = $shipmentConfirmRequestXML->asXML();
		$payload = array( "ShipmentRequest" => json_decode( json_encode( (array) simplexml_load_string( $requestXML ) ), true ) );									
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, json_encode($payload), 'tec' );
		$response = cdi_c_Function::cdi_url_post_remote( $urlshipment, $payload, $headers );
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, $response, 'tec' );		
		
		// Response processing
		$arrayresponse = json_decode( $response, true );							
		if (isset( $arrayresponse['response']['errors'] )) {
			// Technical or Authentification error							
			$returnerrcode = $arrayresponse['response']['errors']['0']['code'];
			$returnerrlibelle = $arrayresponse['response']['errors']['0']['message'];
		}else{
			// API error									
			$returnerrcode = $arrayresponse['ShipmentResponse']['Response']['ResponseStatus']['Code'];
			$returnerrlibelle = $arrayresponse['ShipmentResponse']['Response']['ResponseStatus']['Description'];
		}								
		$returnerrlibellecomplement = null;
		if ( $returnerrcode != 1 ) {
			if ( isset( $arrayresponse['ShipmentResponse']['Response']['Alert']['0'] ) ) {
				$returnerrlibellecomplement = implode( ' ', $arrayresponse['ShipmentResponse']['Alert']['0'] );
			}
			$errorws = __( ' ===> Error stop processing at order #', 'cdi' ) . $array_for_carrier['order_id'] . ' - ' . $returnerrcode . ' : ' . $returnerrlibelle . ' ' . $returnerrlibellecomplement;
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $errorws, 'tec' );
			//cdi_c_Function::cdi_debug( __LINE__, __FILE__, $headers, 'tec' );			
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $payload, 'tec' );			
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $arrayresponse, 'tec' );
			return ;
		}

		$shipmentIdentificationNumber = $arrayresponse['ShipmentResponse']['ShipmentResults']['ShipmentIdentificationNumber'];								
		$shipmentCharges = $arrayresponse['ShipmentResponse']['ShipmentResults']['ShipmentCharges']['TotalCharges']['MonetaryValue'];
		$shipmentChargesCurrency = $arrayresponse['ShipmentResponse']['ShipmentResults']['ShipmentCharges']['TotalCharges']['CurrencyCode'];					
								
		// Extract Label from Response
		$GraphicImage = $arrayresponse['ShipmentResponse']['ShipmentResults']['PackageResults']['0']['ShippingLabel']['GraphicImage'];								
		$HTMLImage = $arrayresponse['ShipmentResponse']['ShipmentResults']['PackageResults']['0']['ShippingLabel']['HTMLImage'];								
		$retparcelnumber = $shipmentIdentificationNumber;
		$parcelNumberPartner = '';								

		// process the data
		$retparcelnumber = $shipmentIdentificationNumber;
		$order->delete_meta_data( '_cdi_meta_parcelnumber_return' );
		$order->add_meta_data( '_cdi_meta_parcelnumber_return', $retparcelnumber );
		$retpdfurl = '';
		$order->delete_meta_data( '_cdi_meta_pdfurl_return' );
		$order->add_meta_data( '_cdi_meta_pdfurl_return', $retpdfurl );
		$order->delete_meta_data( '_cdi_meta_return_executed' );
		$order->add_meta_data( '_cdi_meta_return_executed', 'yes' );
		$base64labelreturn = cdi_c_Pdf_Workshop::cdi_convert_giftopdf( $GraphicImage, 'L', array( '150', '100' ), '90', $order_id ); // Only in 10x15 format
		cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Order : ' . $order_id . ' Parcel : ' . $retparcelnumber, 'msg' );
		if ( $base64labelreturn ) {
			$order->delete_meta_data( '_cdi_meta_base64_return' );
			$order->add_meta_data( '_cdi_meta_base64_return', $base64labelreturn );
		}
		$order->save();
		if ( get_option( 'cdi_o_settings_ups_modetestprod' ) == 'yes' ) {
			cdi_c_Function::cdi_stat( 'UPS-ret' );
		} else {
			cdi_c_Function::cdi_stat( 'UPS-ret-test' );
		}
	}
}




