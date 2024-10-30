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
/* UPS Start Request Oauthcode                                                          */
/****************************************************************************************/

class cdi_c_Ups_Get_Oauthcode {

	public static function init() {
	}

	public static function cdi_Ups_OAuth_gennew_token() {
		include( 'ups-access-context.php' );
		$curl = curl_init();
		$payload = "grant_type=client_credentials";
		curl_setopt_array($curl, [
			CURLOPT_HTTPHEADER => [
				"Content-Type: application/x-www-form-urlencoded",
				"x-merchant-id: string " . $upscomptenumber ,
				"Authorization: Basic " . base64_encode($upsclientidoauth . ":" . $upsclientsecretoauth)
				],
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_URL => $upsurltoken,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => "POST",
			]);
		// Curl response	
		$response = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);	
		if ($error) {
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, "Erreur Curl : " . $error, 'tec' ) ;
			return null ;
		} else {
			$responsedecoded = json_decode($response, true);
			if (isset($responsedecoded["access_token"])) {			
				return $response ;
			} else {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, "Erreur OAuth : " . $responsedecoded["error"], 'tec' ) ;
				return null ;
			}
		}			
	}
	
	public static function cdi_Ups_OAuth_get_token() {
		$token = get_option( 'cdi_o_settings_ups_currenttoken' ) ;
		$datevalid = get_option( 'cdi_o_settings_ups_currenttoken_valdate' ) ;
		$date = time() ; // seconds
		if (!$token || !$datevalid || ($date > $datevalid) ) {
			// Refresh the token
			$response = cdi_c_Ups_Get_Oauthcode::cdi_Ups_OAuth_gennew_token() ;
			//cdi_c_Function::cdi_debug( __LINE__, __FILE__, $response, 'tec' ) ;
			if ($response == null) {
				return null ;
			}			
			$token = json_decode($response)->access_token;			
			// Store here Token and Validity date in seconds
			update_option( 'cdi_o_settings_ups_currenttoken',  $token) ;
			$datevalid = $date + json_decode($response)->expires_in - 600 ; // 10 mn before deadline
			update_option( 'cdi_o_settings_ups_currenttoken_valdate', $datevalid ) ;
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, "New UPS access token generating. Valid until : " . date(DATE_RFC2822, $datevalid), 'tec' ) ;
		}
		return $token ;		
	}
}
?>
