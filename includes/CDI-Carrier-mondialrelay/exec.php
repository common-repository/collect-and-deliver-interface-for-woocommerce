<?php
/*
 * Plugin Name: CDI - Collect and Deliver Interface
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/****************************************************************************************/
/* Carrier mondialrelay                                                                 */
/****************************************************************************************/

class cdi_c_Carrier_mondialrelay {
	public static function init() {
		include_once dirname( __FILE__ ) . '/Mondialrelay-Affranchissement.php';
		cdi_c_Mondialrelay_Affranchissement::init();
		include_once dirname( __FILE__ ) . '/Mondialrelay-Retourcolis.php';
		cdi_c_Mondialrelay_Retourcolis::init();

		global $MRerrcodlist;
		$MRerrcodlist = array(
			'1' => 'Enseigne invalide',
			'2' => "Numéro d'enseigne vide ou inexistant",
			'3' => 'Numéro de compte enseigne invalide',
			'5' => 'Numéro de dossier enseigne invalide',
			'7' => 'Numéro de client enseigne invalide(champ NCLIENT)',
			'8' => 'Mot de passe ou hachage invalide',
			'9' => 'Ville non reconnu ou non unique',
			'10' => 'Type de collecte invalide',
			'11' => 'Numéro de Relais de Collecte invalide',
			'12' => 'Pays de Relais de collecte invalide',
			'13' => 'Type de livraison invalide',
			'14' => 'Numéro de Relais de livraison invalide',
			'15' => 'Pays de Relais de livraison invalide',
			'20' => 'Poids du colis invalide',
			'21' => 'Taille (Longueur + Hauteur) du colis invalide',
			'22' => 'Taille du Colis invalide',
			'24' => "Numéro d'expédition ou de suivi invalide",
			'26' => 'Temps de montage invalide',
			'27' => 'Mode de collecte ou de livraison invalide',
			'28' => 'Mode de collecte invalide',
			'29' => 'Mode de livraison invalide',
			'30' => 'Adresse (L1) invalide',
			'31' => 'Adresse (L2) invalide',
			'33' => 'Adresse (L3) invalide',
			'34' => 'Adresse (L4) invalide',
			'35' => 'Ville invalide',
			'36' => 'Code postal invalide',
			'37' => 'Pays invalide',
			'38' => 'Numéro de téléphone invalide',
			'39' => 'Adresse e-mail invalide',
			'40' => 'Paramètres manquants',
			'42' => 'Montant CRT invalide',
			'43' => 'Devise CRT invalide',
			'44' => 'Valeur du colis invalide',
			'45' => 'Devise de la valeur du colis invalide',
			'46' => "Plage de numéro d'expédition épuisée",
			'47' => 'Nombre de colis invalide',
			'48' => 'Multi-Colis Relais Interdit',
			'49' => 'Action invalide',
			'60' => "Champ texte libre invalide (Ce code erreur n'est pas invalidant)",
			'61' => 'Top avisage invalide',
			'62' => 'Instruction de livraison invalide',
			'63' => 'Assurance invalide',
			'64' => 'Temps de montage invalide',
			'65' => 'Top rendez-vous invalide',
			'66' => 'Top reprise invalide',
			'67' => 'Latitude invalide',
			'68' => 'Longitude invalide',
			'69' => 'Code Enseigne invalide',
			'70' => 'Numéro de Point Relais invalide',
			'71' => 'Nature de point de vente non valide',
			'74' => 'Langue invalide',
			'78' => 'Pays de Collecte invalide',
			'79' => 'Pays de Livraison invalide',
			'80' => 'Code tracing : Colis enregistré',
			'81' => 'Code tracing : Colis en traitement chez Mondial Relay',
			'82' => 'Code tracing : Colis livré',
			'83' => 'Code tracing : Anomalie',
			'84' => '(Réservé Code Tracing)',
			'85' => '(Réservé Code Tracing)',
			'86' => '(Réservé Code Tracing)',
			'87' => '(Réservé Code Tracing)',
			'88' => '(Réservé Code Tracing)',
			'89' => '(Réservé Code Tracing)',
			'92' => 'Le code pays du destinataire et le code pays du Point Relais doivent être identiques.Ou Solde insuffisant (comptes prépayés)',
			'93' => "Aucun élément retourné par le plan de triSi vous effectuez une collecte ou une livraison en Point Relais, vérifiez que les Point Relais sont bien disponibles. Si vous effectuez une livraison à domicile, il est probable que le code postal que vous avez indiquén'existe pas.",
			'94' => 'Colis Inexistant',
			'95' => 'Compte Enseigne non activé',
			'96' => "Type d'enseigne incorrect en Base",
			'97' => 'Clé de sécurité invalideCf. : § «Génération de la clé de sécurité»',
			'98' => 'Erreur générique (Paramètres invalides)',
			'99' => 'Erreur générique du service chez MR (à notifier à Mondial Relay en précisant la date et l\'heure de la requête ainsi que les paramètres envoyés
afin d\'effectuer une vérification)',
		);
	}

	/****************************************************************************************/
	/* Carrier References Livraisons                                                        */
	/****************************************************************************************/

	public static function cdi_carrier_update_settings() {
		$forcesettings = get_option( 'cdi_o_settings_mondialrelay_defautsettings' );
		if ( $forcesettings == 'yes' ) {
			$arrdefaut_mondialrelay_pickups = array( 'cdi_shipping_mondialrelay_pick1', 'cdi_shipping_mondialrelay_pick2' );
			$pickuplist = str_replace( ' ', '', get_option( 'cdi_o_settings_pickupmethodnames' ) );
			$arraypickuplist = explode( ',', $pickuplist );
			$arraypickuplist = array_map( 'trim', $arraypickuplist );
			foreach ( $arrdefaut_mondialrelay_pickups as $defaut_mondialrelay_pickup ) {
				if ( ! in_array( $defaut_mondialrelay_pickup, $arraypickuplist ) ) {
					$pickuplist = trim( $pickuplist, ' ,' );
					$pickuplist = $pickuplist . ',' . $defaut_mondialrelay_pickup;
					$pickuplist = str_replace( ',,', ',', $pickuplist );
					$pickuplist = trim( $pickuplist, ' ,' );
					update_option( 'cdi_o_settings_pickupmethodnames', $pickuplist );
				}
			}
			$arrdefaut_mondialrelay_products = array( 'cdi_shipping_mondialrelay_home1=LD1', 'cdi_shipping_mondialrelay_home2=LD2', 'cdi_shipping_mondialrelay_pick1=24R' );
			$productlist = str_replace( ' ', '', get_option( 'cdi_o_settings_forcedproductcodes' ) );
			$arrayproductlist = explode( ',', $productlist );
			$arrayproductlist = array_map( 'trim', $arrayproductlist );
			foreach ( $arrdefaut_mondialrelay_products as $defaut_mondialrelay_product ) {
				if ( ! in_array( $defaut_mondialrelay_product, $arrayproductlist ) ) {
					$productlist = trim( $productlist, ' ,' );
					$productlist = $productlist . ',' . $defaut_mondialrelay_product;
					$productlist = str_replace( ',,', ',', $productlist );
					$productlist = trim( $productlist, ' ,' );
					update_option( 'cdi_o_settings_forcedproductcodes', $productlist );
				}
			}

			$arrdefaut_mondialrelay_mandatoryphones = array( 'cdi_shipping_mondialrelay_pick1', 'cdi_shipping_mondialrelay_pick2' );
			$mandatoryphonelist = str_replace( ' ', '', get_option( 'cdi_o_settings_phonemandatory' ) );
			$arraymandatoryphonelist = explode( ',', $mandatoryphonelist );
			$arraymandatoryphonelist = array_map( 'trim', $arraymandatoryphonelist );
			foreach ( $arrdefaut_mondialrelay_mandatoryphones as $defaut_mondialrelay_mandatoryphone ) {
				if ( ! in_array( $defaut_mondialrelay_mandatoryphone, $arraymandatoryphonelist ) ) {
					$mandatoryphonelist = trim( $mandatoryphonelist, ' ,' );
					$mandatoryphonelist = $mandatoryphonelist . ',' . $defaut_mondialrelay_mandatoryphone;
					$mandatoryphonelist = str_replace( ',,', ',', $mandatoryphonelist );
					$mandatoryphonelist = trim( $mandatoryphonelist, ' ,' );
					update_option( 'cdi_o_settings_phonemandatory', $mandatoryphonelist );
				}
			}
		}
	}

	public static function cdi_isit_pickup_authorized() {
		global $woocommerce;
		$country = $woocommerce->customer->get_shipping_country();
		$return = ( in_array( $country, explode( ',', get_option( 'cdi_o_settings_mondialrelay_Gp1Codes' ) ) )
			  or in_array( $country, explode( ',', get_option( 'cdi_o_settings_mondialrelay_Gp2Codes' ) ) )
			  or in_array( $country, explode( ',', get_option( 'cdi_o_settings_mondialrelay_Gp3Codes' ) ) ) );
		return $return;
	}

	public static function cdi_test_carrier() {
		return true;
	}

	public static function cdi_get_points_livraison( $relaytype ) {
		global $woocommerce;
		global $msgtofrontend;
		global $MRerrcodlist;
		if ( self::cdi_test_carrier() === false ) {
			return false;
		}
		// Shipping address GPS
		$arraygps = cdi_c_Function::cdi_geolocate_customer();
		if ( ! $arraygps ) {
			return false;
		}
		$lat = $arraygps['lat'];
		$lon = $arraygps['lon'];
		$addresscustomer = $arraygps['addresscustomer'];

		// Forced product code
		$chosen_shipping = WC()->session->get( 'cdi_refshippingmethod' );
		$arraychosen = explode( ':', $chosen_shipping ); // explode = method : instance : suffixe
		$forcedproductcode = get_option( 'cdi_o_settings_forcedproductcodes' );
		$arrayforcedproductcode = explode( ',', $forcedproductcode );
		$arrayforcedproductcode = array_map( 'trim', $arrayforcedproductcode );
		$codeproductfound = '';
		foreach ( $arrayforcedproductcode as $relation ) {
			$arrayrelation = explode( '=', $relation );
			if ( isset( $arraychosen[1] ) ) { // test case for legacy shipping method non WC 2.6
				$arraychosenun = $arraychosen[0] . ':' . $arraychosen[1];
			} else {
				$arraychosenun = $arraychosen[0];
			}
			if ( $arrayrelation[0] && ( ( $arrayrelation[0] == $arraychosen[0] ) or ( $arrayrelation[0] == $arraychosenun ) ) ) {
				$codeproductfound = $arrayrelation[1];
			}
		}

		$errorws = null;

		$MR_WebSiteId = get_option( 'cdi_o_settings_mondialrelay_contractnumber' );
		$MR_WebSiteKey = get_option( 'cdi_o_settings_mondialrelay_password' );

		$client = new nusoap_client( 'https://api.mondialrelay.com/Web_Services.asmx?WSDL', true );
		$client->soap_defencoding = 'utf-8';

		$Pays = $woocommerce->customer->get_shipping_country();
		$NumPointRelais = '';
		$Ville = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $woocommerce->customer->get_shipping_city() ) ) );
		$CP = $woocommerce->customer->get_shipping_postcode();

		if ( $lat and $lon ) {
			// Forced, because MR lat-lon works with only 6 or 7 decimal digits
			$lat = number_format( $lat, 7, '.', ',' );
			$lon = number_format( $lon, 7, '.', ',' );
			$CP = null;
			$Ville = null;
		}
		$Latitude = $lat;
		$Longitude = $lon;
		$Taille = '';

		$Poids = (float) $woocommerce->cart->cart_contents_weight;
		if ( get_option( 'woocommerce_weight_unit' ) == 'kg' ) { // Convert kg to g
			$Poids = $Poids * 1000;
		}
		$Poids = round( $Poids + get_option( 'cdi_o_settings_parceltareweight' ) );
		if ( ! $Poids or $Poids == 0 ) {
			$Poids = 100;
		}

		$Action = $codeproductfound;
		$DelaiEnvoi = 1;
		$RayonRecherche = '';
		$TypeActivite = '';
		$NACE = '';
		$NombreResultats = 30;

		$params = array(
			'Enseigne' => $MR_WebSiteId,
			'Pays' => $Pays,
			'NumPointRelais' => $NumPointRelais,
			'Ville' => $Ville,
			'CP' => $CP,
			'Latitude' => $Latitude,
			'Longitude' => $Longitude,
			'Taille' => $Taille,
			'Poids' => $Poids,
			'Action' => $Action,
			'DelaiEnvoi' => $DelaiEnvoi,
			'RayonRecherche' => $RayonRecherche,
			'TypeActivite' => $TypeActivite,
			'NACE' => $NACE,
			'NombreResultats' => $NombreResultats,
		);
		$code = implode( '', $params );
		$code .= $MR_WebSiteKey;
		$params['Security'] = strtoupper( md5( $code ) );

		$result = $client->call(
			'WSI4_PointRelais_Recherche',
			$params,
			'https://api.mondialrelay.com/',
			'https://api.mondialrelay.com/WSI4_PointRelais_Recherche'
		);
		if ( $client->fault ) {
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client, 'tec' );

			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->request, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->response, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->getDebug(), 'tec' );
			$msgtofrontend = __( ' ===> Search relays error  -', 'cdi' ) . ' - Mondial Relay : Fault (Expect -The request contains an invalid SOAP body)';
		} else {
			$err = $client->getError();
			if ( $err ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $err, 'tec' );				
				$errorws = __( ' ===> Search relays error  -', 'cdi' ) . 'Mondial Relay : ' . $err ;
			} else {
				$errorid = $result['WSI4_PointRelais_RechercheResult']['STAT'];
				if ( $errorid != 0 ) {
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
					$errorws = __( ' ===> Search Points Relay error  - ', 'cdi' ) . 'Mondial Relay : ' . $errorid . ' : ' . $MRerrcodlist[ $errorid ];
				} else {
					// process the return
					$returnrelays = array();
					$PointsRelais = $result['WSI4_PointRelais_RechercheResult']['PointsRelais']['PointRelais_Details'];
					foreach ( $PointsRelais as $point ) {
						$arr = array(
							'codePays' => $point['Pays'],
							'langue' => 'FR',
							'libellePays' => WC()->countries->countries[ $point['Pays'] ],
							'loanOfHandlingTool' => null,
							'parking' => null,
							'reseau' => '24R',      // Mettre dynamiquement code product
							'accesPersonneMobiliteReduite' => null,
							'adresse1' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['LgAdr3'] ) ),
							'adresse2' => rtrim( cdi_c_Function::cdi_sanitize_voie( isset( $point['LgAdr2'] ) ? $point['LgAdr2'] : '' ) ),
							'adresse3' => rtrim( cdi_c_Function::cdi_sanitize_voie( isset( $point['LgAdr4'] ) ? $point['LgAdr4'] : '' ) ),
							'codePostal' => preg_replace( '/[^a-zA-Z0-9\s]/', '', $point['CP'] ),
							'coordGeolocalisationLatitude' => (float) str_replace( ',', '.', $point['Latitude'] ),
							'coordGeolocalisationLongitude' => (float) str_replace( ',', '.', $point['Longitude'] ),
							'distanceEnMetre' => $point['Distance'],
							'horairesOuvertureLundi' => $point['Horaires_Lundi']['string']['0'] . '-' . $point['Horaires_Lundi']['string']['1'] . ' ' . $point['Horaires_Lundi']['string']['2'] . '-' . $point['Horaires_Lundi']['string']['3'],
							'horairesOuvertureMardi' => $point['Horaires_Mardi']['string']['0'] . '-' . $point['Horaires_Mardi']['string']['1'] . ' ' . $point['Horaires_Mardi']['string']['2'] . '-' . $point['Horaires_Mardi']['string']['3'],
							'horairesOuvertureMercredi' => $point['Horaires_Mercredi']['string']['0'] . '-' . $point['Horaires_Mercredi']['string']['1'] . ' ' . $point['Horaires_Mercredi']['string']['2'] . '-' . $point['Horaires_Mercredi']['string']['3'],
							'horairesOuvertureJeudi' => $point['Horaires_Jeudi']['string']['0'] . '-' . $point['Horaires_Jeudi']['string']['1'] . ' ' . $point['Horaires_Jeudi']['string']['2'] . '-' . $point['Horaires_Jeudi']['string']['3'],
							'horairesOuvertureVendredi' => $point['Horaires_Vendredi']['string']['0'] . '-' . $point['Horaires_Vendredi']['string']['1'] . ' ' . $point['Horaires_Vendredi']['string']['2'] . '-' . $point['Horaires_Vendredi']['string']['3'],
							'horairesOuvertureSamedi' => $point['Horaires_Samedi']['string']['0'] . '-' . $point['Horaires_Samedi']['string']['1'] . ' ' . $point['Horaires_Samedi']['string']['2'] . '-' . $point['Horaires_Samedi']['string']['3'],
							'horairesOuvertureDimanche' => $point['Horaires_Dimanche']['string']['0'] . '-' . $point['Horaires_Dimanche']['string']['1'] . ' ' . $point['Horaires_Dimanche']['string']['2'] . '-' . $point['Horaires_Dimanche']['string']['3'],
							'identifiant' => ltrim( $point['Num'] ),
							'indiceDeLocalisation' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['Localisation1'] ) ),
							'localite' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['Ville'] ) ),
							'nom' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['LgAdr1'] ) ),
							'poidsMaxi' => '30000',
							'typeDePoint' => $codeproductfound,
							'URL_Photo' => isset( $point['URL_Photo'] ) ? $point['URL_Photo'] : '',
							'URL_Plan' => isset( $point['URL_Plan'] ) ? $point['URL_Plan'] : '',
						);
						$obj = (object) $arr;
						$returnrelays[] = $obj;
					}
					$listrelays = (object) array(
						'errorCode' => 0,
						'errorMessage' => 'Code retour OK',
						'listePointRetraitAcheminement' => $returnrelays,
					);
					$return = (object) array( 'return' => $listrelays );
				}
			}
		}
		// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $PointsRelais, 'tec');
		if ( $errorws ) {
			return false;
		} else {
			return $return;
		}
	}

	public static function cdi_check_pickup_and_location() {
		$codeproductfound = WC()->session->get( 'cdi_forcedproductcode' );
		$cdipickuplocationid = WC()->session->get( 'cdi_pickuplocationid' );
		if ( in_array( $codeproductfound, array( '24R', '24L', 'DRI' ) ) and empty( $cdipickuplocationid ) ) { // error to catch
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $codeproductfound, 'tec' );
			throw new Exception( __( 'Pickup location - Technical error on pickup product code vs location id. Please try again.', 'cdi' ) );
		}
	}

	/****************************************************************************************/
	/* Carrier Front-end tracking                                                           */
	/****************************************************************************************/

	public static function cdi_text_preceding_trackingcode() {
		$return = __( get_option( 'cdi_o_settings_mondialrelay_text_preceding_trackingcode' ), 'cdi' );
		return $return;
	}

	public static function cdi_url_trackingcode() {
		$return = get_option( 'cdi_o_settings_mondialrelay_url_trackingcode' );
		return $return;
	}

	/****************************************************************************************/
	/* CDI Metabox in order panel                                                           */
	/****************************************************************************************/

	public static function cdi_metabox_initforcarrier( $order_id, $order ) {
		return true;
	}

	public static function cdi_metabox_tracking_zone( $order_id, $order ) {
		?>
		<div style='background-color:#eeeeee; color:#000000; width:100%;'>Tracking zone</div><p style="clear:both"></p>

		<p style='width:35%; float:left;  margin-top:5px;'><a><?php _e( 'Tracking code : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_tracking',
																				'type' => 'text',
																				'style' => 'width:60%; float:left;',
																				'id'   => '_cdi_meta_tracking',
																				'label' => '',
																			), $order,
																		);
																		?>
																 </a></p><p style="clear:both"></p>

		  <?php $cdi_parcelNumberPartner = $order->get_meta( '_cdi_meta_parcelNumberPartner' ); ?>  
		  <?php if ( $cdi_parcelNumberPartner ) { ?>  
				<p><a><?php _e( 'Partner number : ', 'cdi' ); ?></a><a style='color:black'><?php echo esc_attr( $cdi_parcelNumberPartner ); ?> </a></p>
		<?php } ?>

		  <?php $cdi_urllabel = $order->get_meta( '_cdi_meta_urllabel' ); ?>
		  <?php if ( $cdi_urllabel ) { ?> 
				<p><a style="display:inline-block;"><?php _e( 'To Labels :  ', 'cdi' ); ?></a><a style="vertical-align: middle; display:inline-block; color:black; width:12em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" href="<?php echo esc_url( $cdi_urllabel ); ?>" onclick="window.open(this.href); return false;" > <?php echo esc_url( $cdi_urllabel ); ?> </a></p>
		<?php } ?>

		  <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true or $order->get_meta( '_cdi_meta_exist_uploads_cn23' ) == true ) { ?>
		  <p style="display:inline-block; margin:0px;">
		<?php } ?>

			 <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true ) { ?>
					 <form method="post" id="cdi_local_label_pdf" action="" style="display:inline-block;">
					  <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
					  <input type="submit" name="cdi_local_label_pdf" value="Print label"  title="Print label" /> 
					  <?php wp_nonce_field( 'cdi_local_label_pdf', 'cdi_local_label_pdf_nonce' ); ?> 
					</form>
		   <?php } ?>

			 <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_cn23' ) == true ) { ?>
					 <form method="post" id="cdi_local_cn23_pdf" action="" style="display:inline-block;">
					  <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
					  <input type="submit" name="cdi_local_cn23_pdf" value="Print Cn23"  title="Print cn23" /> 
					  <?php wp_nonce_field( 'cdi_local_cn23_pdf', 'cdi_local_cn23_pdf_nonce' ); ?> 
					</form>
		   <?php } ?>
		</p>
		<?php
	}

	public static function cdi_metabox_parcel_settings( $order_id, $order ) {
		?>
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Parcel parameters', 'cdi' ); ?></div><p style="clear:both"></p>
		<p style='width:25%; float:left;  margin-top:5px;'><a><?php _e( 'Parcel : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_typeparcel',
																				'type' => 'text',
																				'options' => array(
																					'colis-standard'   => __( 'Standard', 'cdi' ),
																					'colis-volumineux' => __( 'Cumbersome', 'cdi' ),
																					'colis-rouleau   ' => __( 'Tube', 'cdi' ),
																				),
																				'style' => 'width:70%; float:left;',
																				'id'   => '_cdi_meta_typeparcel',
																				'label' => '',
																			), $order,
																		);
																		?>
																 </a></p><p style="clear:both"></p>
		<p style='width:25%; float:left;  margin-top:5px;'><a><?php _e( 'Weight : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_parcelweight',
																				'type' => 'text',
																				'data_type' => 'decimal',
																				'style' => 'width:70%; float:left;',
																				'id'   => '_cdi_meta_parcelweight',
																				'label' => '',
																			), $order,
																		);
																		?>
																 </a></p><p style="clear:both"></p>
		<p style='width:35%; float:left; margin-top:5px;'><a><?php _e( 'Collect : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_collectcar',
																				'type' => 'text',
																				'options' => array(
																					'' => '',
																					'REL' => __( 'REL - Collecte point relais', 'cdi' ),
																					'CDR' => __( 'CDR - Collecte domicile 1P', 'cdi' ),
																					'CDS' => __( 'CDS - Collecte domicile 2P (lourd)', 'cdi' ),
																					'CCC' => __( 'CCC - Collecte Client Chargeur', 'cdi' ),
																				),
																				'style' => 'width:60%; float:left;',
																				'id'   => '_cdi_meta_collectcar',
																				'label' => '',
																			), $order,
																		);
																		?>
																 </a></p><p style="clear:both"></p>   
		<?php
	}

	public static function cdi_metabox_optional_choices( $order_id, $order ) {
		?>
		<?php $shipping_country = $order->get_shipping_country(); ?>
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Optional services', 'cdi' ); ?></div><p style="clear:both"></p>
		<p style='width:50%; float:left;  margin-top:0px;'><a><?php _e( 'Compensation + : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_additionalcompensation',
																				'type' => 'text',
																				'options' => array(
																					'yes' => __( 'yes', 'cdi' ),
																					'no' => __( 'no', 'cdi' ),
																				),
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_additionalcompensation',
																				'label' => '',
																			), $order,
																		);
																		?>
																 </a></p><p style="clear:both"></p>

		  <?php
			if ( $order->get_meta( '_cdi_meta_additionalcompensation' ) == 'yes' ) {
				?>
				 <!--  Amount compensation display --> 
		<p style='width:30%; float:left; margin-left:20%; margin-top:5px;'><a><?php _e( 'Amount : ', 'cdi' ); ?>
																						<?php
																						woocommerce_wp_text_input(
																							array(
																								'name' => '_cdi_meta_amountcompensation',
																								'type' => 'text',
																								'data_type' => 'decimal',
																								'style' => 'width:45%; float:left;',
																								'id'   => '_cdi_meta_amountcompensation',
																								'label' => '',
																							), $order,
																						);
																						?>
																				 </a></p><p style="clear:both"></p>

		  <?php } ?> <!--  End Amount compensation display --> 
		<?php
	}

	public static function cdi_metabox_shipping_customer_choices( $order_id, $order ) {
		?>
		<!--  Pickup location web services - can be filled by meta box or retraitpoint web services --> 
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Customer shipping settings :', 'cdi' ); ?></div><p style="clear:both"></p>
		<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Forced product code : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_productCode',
																				'type' => 'text',
																				'options' => array(
																					'' => '',
																					'24R' => __( '24R - Point relais', 'cdi' ),
																					'24L' => __( '24L - Point relais XL', 'cdi' ),
																					'DRI' => __( 'DRI - Colis drive', 'cdi' ),
																					'LD1' => __( 'LD1 - Domicile avec RdV 1P', 'cdi' ),
																					'LDS' => __( 'LDS - Domicile avec RdV 2P', 'cdi' ),
																				),
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_productCode',
																				'label' => '',
																			), $order,
																		);
																		?>
																 </a></p><p style="clear:both"></p>
		<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Pickup location id : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_pickupLocationId',
																				'type' => 'text',
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_pickupLocationId',
																				'label' => '',
																			), $order,
																		);
																		?>
																 </a></p><p style="clear:both"></p>

		  <?php $pickupLocationlabel = $order->get_meta( '_cdi_meta_pickupLocationlabel' ); ?>
		  <?php if ( $pickupLocationlabel ) { ?>
				<?php $pickupLocationlabel = stristr( $pickupLocationlabel, '=> Distance: ', true ); ?>
				<p><a><?php _e( 'Location : ', 'cdi' ); ?></a><a style='color:black'><?php echo wp_kses_post( $pickupLocationlabel ); ?> </a></p>
		<?php } ?>
		<!--  End Pickup location web services --> 
		<?php
	}

	public static function cdi_metabox_shipping_cn23( $order_id, $order ) {
	}

	public static function cdi_metabox_parcel_return( $order_id, $order ) {
		?>
		<?php
		if ( get_option( 'cdi_o_settings_mondialrelay_parcelreturn' ) == 'yes' ) {
			?>
			 <!--  Parcel return display --> 
		  <div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Parcel return', 'cdi' ); ?></div>        		 
		  	<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Return days : ', 'cdi' ); ?>
																					  <?php
																						woocommerce_wp_text_input(
																							array(
																								'name' => '_cdi_meta_nbdayparcelreturn',
																								'type' => 'text',
																								'data_type' => 'decimal',
																								'style' => 'width:45%; float:left;',
																								'id'   => '_cdi_meta_nbdayparcelreturn',
																								'label' => '',
																							), $order, 
																						);
																						?>
																			 </a></p><p style="clear:both"></p>

			<?php if ( $order->get_meta( '_cdi_meta_base64_return' ) ) { ?>
			<p style="display:inline-block; margin:0px;">
			   <form method="post" id="cdi_admin_return_pdf" action="" style="display:inline-block;">
				 <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				 <input type="submit" name="cdi_admin_return_pdf" value="Print label"  title="Print return label" /> 
				 <?php wp_nonce_field( 'cdi_admin_return_pdf', 'cdi_admin_return_pdf_nonce' ); ?> 
			   </form>
 
				<?php $cdi_urllabel_return = $order->get_meta( '_cdi_meta_pdfurl_return' ); ?>
			  <p><a style="display:inline-block;"><?php _e( 'To return label : ', 'cdi' ); ?></a><a style="vertical-align: middle; display:inline-block; color:black; width:12em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" href="<?php echo esc_url( $cdi_urllabel_return ); ?>" onclick="window.open(this.href); return false;" > <?php echo esc_url( $cdi_urllabel_return ); ?> </a></p>

				<?php $cdi_tracking_return = $order->get_meta( '_cdi_meta_parcelnumber_return' ); ?>  
			  <p><a><?php _e( 'Return tracking code : ', 'cdi' ); ?></a><a style='color:black'><?php echo esc_attr( $cdi_tracking_return ); ?> </a></p>

		   </p>
		   <?php } else { ?>         
				<?php if ( cdi_c_Retour_Colis::cdi_check_returnlabel_eligible( $order_id ) == true ) { ?>   
			   <p style="display:inline-block; margin:0px;">    
			   <form method="post" id="cdi_admin_createreturnlabel_pdf" action="" style="display:inline-block; padding-left:30px;">
				 <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				 <input type="submit" name="cdi_admin_createreturnlabel_pdf" value="Label"  title="<?php _e( 'Force the creation of a Label return form for this order. It will be seen by the admin, and the Internet customer in his order view. ', 'cdi' ); ?>" style='width: 60px; height: 30px; border: solid 1px #000; border-radius: 50%;'  /> 
					<?php wp_nonce_field( 'cdi_admin_createreturnlabel_pdf', 'cdi_admin_createreturnlabel_pdf_nonce' ); ?> 
			   </form> 
			<?php } ?>
		  <?php } ?>

		<?php } ?> <!--  End Parcel return display --> 
		<?php
	}

	public static function cdi_metabox_shipping_updatepickupaddress( $order_id, $order ) {
		global $woocommerce;
		$pickupLocationId = $order->get_meta( '_cdi_meta_pickupLocationId' );
		$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );

		$MR_WebSiteId = get_option( 'cdi_o_settings_mondialrelay_contractnumber' );
		$MR_WebSiteKey = get_option( 'cdi_o_settings_mondialrelay_password' );
		$client = new nusoap_client( 'https://api.mondialrelay.com/Web_Services.asmx?WSDL', true );
		$client->soap_defencoding = 'utf-8';
		$Pays = $array_for_carrier['shipping_country'];
//		$NumPointRelais = $array_for_carrier['pickup_Location_id'];
		$NumPointRelais = $pickupLocationId;
		$NombreResultats = 1;
		$params = array(
			'Enseigne' => $MR_WebSiteId,
			'Pays' => $Pays,
			'NumPointRelais' => $NumPointRelais,
			'NombreResultats' => $NombreResultats,
		);
		$code = implode( '', $params );
		$code .= $MR_WebSiteKey;
		$params['Security'] = strtoupper( md5( $code ) );
		$result = $client->call(
			'WSI4_PointRelais_Recherche',
			$params,
			'https://api.mondialrelay.com/',
			'https://api.mondialrelay.com/WSI4_PointRelais_Recherche'
		);
		if ( $client->fault ) {
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client, 'tec' );

			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->request, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->response, 'tec' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $client->getDebug(), 'tec' );
			$errorws = __( ' ===> Search relays error  -', 'cdi' ) . ' - Mondial Relay : Fault (Expect -The request contains an invalid SOAP body)';
		} else {
			$err = $client->getError();
			if ( $err ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $err, 'tec' );
				$errorws = __( ' ===> Error on change pickupid in admin : ', 'cdi' ) . $err ;
			}
			if ( isset( $result['WSI4_PointRelais_RechercheResult']['PointsRelais']['PointRelais_Details'] ) ) {
				$point = $result['WSI4_PointRelais_RechercheResult']['PointsRelais']['PointRelais_Details'];
				$pointabstractlabel = cdi_c_Function::cdi_sanitize_voie( trim( $point['LgAdr1'] ) ) . ' =&gt; ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $point['LgAdr3'] ) ) . ' ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $point['LgAdr2'] ) ) . ' ' .
							  $point['CP'] . ' ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $point['Ville'] ) ) . ' =&gt; Distance: ' .
							  '?' . 'm =&gt; Id: ' .
							  $point['Num'];
				$pointabstractlabel = htmlspecialchars_decode( $pointabstractlabel );
				$order->update_meta_data( '_cdi_meta_pickupLocationlabel', $pointabstractlabel );
				$pickupfulladdress = array();
				$pickupfulladdress['nom'] = cdi_c_Function::cdi_sanitize_voie( trim( $point['LgAdr1'] ) );
				$pickupfulladdress['adresse1'] = cdi_c_Function::cdi_sanitize_voie( trim( $point['LgAdr3'] ) );
				$pickupfulladdress['adresse2'] = cdi_c_Function::cdi_sanitize_voie( trim( $point['LgAdr2'] ) );
				$pickupfulladdress['adresse3'] = cdi_c_Function::cdi_sanitize_voie( trim( $point['LgAdr4'] ) );
				$pickupfulladdress['codePostal'] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $point['CP'] );
				$pickupfulladdress['localite'] = cdi_c_Function::cdi_sanitize_voie( trim( $point['Ville'] ) );
				$pickupfulladdress['codePays'] = $point['Pays'];
				$pickupfulladdress['libellePays'] = WC()->countries->countries[ $point['Pays'] ];
				$order->update_meta_data( '_cdi_meta_pickupfulladdress', $pickupfulladdress );
			} else {
				$order->update_meta_data( '_cdi_meta_pickupLocationlabel', __( 'Non-existent point', 'cdi' ) . '=> Distance: ' );
				$order->update_meta_data( '_cdi_meta_pickupfulladdress', '' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Error on change pickupid in admin : ' . $order_id . ' - ' . $pickupLocationId, 'msg' );
			}
			$order->save();
		}
	}

	/****************************************************************************************/
	/* CDI Parcel returns                                                                   */
	/****************************************************************************************/

	public static function cdi_prodlabel_parcelreturn( $id_order, $productcode ) {
		cdi_c_Mondialrelay_Retourcolis::cdi_mondialrelay_calc_parcelretour( $id_order, $productcode );
	}

	public static function cdi_isitopen_parcelreturn() {
		$return = get_option( 'cdi_o_settings_mondialrelay_parcelreturn' );
		return $return;
	}

	public static function cdi_isitvalidorder_parcelreturn() {
		return true;
	}

	public static function cdi_text_inviteprint_parcelreturn() {
		$return = get_option( 'cdi_o_settings_mondialrelay_text_preceding_printreturn' );
		return $return;
	}

	public static function cdi_url_carrier_following_parcelreturn() {
		return null;
	}

	public static function cdi_whichproducttouse_parcelreturn( $shippingcountry ) {
		// Product LCC or 24R will be compute in Mondialrelay-Retourcolis.php file, depending on settigs
		return '24R';
	}

	public static function cdi_text_preceding_parcelreturn() {
		$return = get_option( 'cdi_o_settings_mondialrelay_text_preceding_parcelreturn' );
		return $return;
	}

	/****************************************************************************************/
	/* CDI fonctions                                                                        */
	/****************************************************************************************/

	public static function cdi_function_withoutsign_country( $country ) {
		return true;
	}

	public static function cdi_whereis_parcel( $order_id, $trackingcode ) {
		global $MRerrcodlist;
		  $order = wc_get_order( $order_id );
		  $order_date_obj = $order->get_date_created();
		  $order_date = $order_date_obj->format( 'Y-m-d' );
		  $limitdate = str_replace( '-', '', date( 'Y-m-d', strtotime( '-30 days' ) ) );
		  $checkeddate = str_replace( '-', '', substr( $order_date, 0, 10 ) );
		  $datetime1 = new DateTime( $limitdate );
		  $datetime2 = new DateTime( $checkeddate );
		  $difference = $datetime1->diff( $datetime2 );
		if ( $difference->invert > 0 ) {
			return '*** Plus de suivi au-dela de 30 jours.';
		} else {
			// Initiate structure

			$MR_WebSiteId = get_option( 'cdi_o_settings_mondialrelay_contractnumber' );
			$MR_WebSiteKey = get_option( 'cdi_o_settings_mondialrelay_password' );

			$client = new nusoap_client( 'https://api.mondialrelay.com/Web_Services.asmx?WSDL', true );
			$client->soap_defencoding = 'utf-8';
			$Expedition = $trackingcode;
			$Langue = 'FR';
			$params = array(
				'Enseigne' => $MR_WebSiteId,
				'Expedition' => $Expedition,
				'Langue' => $Langue,
			);
			$code = implode( '', $params );
			$code .= $MR_WebSiteKey;
			$params['Security'] = strtoupper( md5( $code ) );
			$result = $client->call(
				'WSI2_TracingColisDetaille',
				$params,
				'https://api.mondialrelay.com/',
				'https://api.mondialrelay.com/WSI2_TracingColisDetaille'
			);
			if ( $client->fault ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
				return '*** Incident soap suivi';
			} else {
				$err = $client->getError();
				if ( $err ) {
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'tec' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result, 'tec' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $err, 'tec' );				
					$errorws = __( ' ===> Error on parcel tracking -', 'cdi' ) . 'Mondial Relay : ' . $err ;
					return __( '*** Erreur suivi : ', 'cdi' ) . $err ;
				} else {
					$errorid = $result['WSI2_TracingColisDetailleResult']['STAT'];
					if ( $errorid != 0 and ( $errorid < 80 or $errorid > 89 ) ) {
						// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $params, 'tec');
						// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $result, 'tec');
						return __( '*** Erreur suivi : ', 'cdi' ) . $errorid . ' - ' . $MRerrcodlist[ $errorid ];
					} else {
						$msgsuivicolis = '=> ' . $errorid . ' - ' . $MRerrcodlist[ $errorid ];
						if ( $result['WSI2_TracingColisDetailleResult']['Libelle01'] ) {
							$msgsuivicolis .= ' | ' . $result['WSI2_TracingColisDetailleResult']['Libelle01'];
						}
						if ( $result['WSI2_TracingColisDetailleResult']['Relais_Libelle'] ) {
							$msgsuivicolis .= ' | ' . $result['WSI2_TracingColisDetailleResult']['Relais_Libelle'];
						}
						if ( $result['WSI2_TracingColisDetailleResult']['Relais_Num'] ) {
							$msgsuivicolis .= ' | ' . $result['WSI2_TracingColisDetailleResult']['Relais_Num'];
						}
						if ( $result['WSI2_TracingColisDetailleResult']['Libelle02'] ) {
							$msgsuivicolis .= ' | ' . $result['WSI2_TracingColisDetailleResult']['Libelle02'];
						}
						return $msgsuivicolis;
					}
				}
			}
		}
	}

	public static function cdi_nochoicereturn_country( $country ) {
		return true;
	}

	/****************************************************************************************/
	/* CDI Bordereau de remise (dépôt)                                                      */
	/****************************************************************************************/

	public static function cdi_prod_remise_bordereau( $selected ) {
		$message = __( 'No delivery slip (deposit) for Mondial Relay.', 'cdi' );
		return $message;
	}


	/****************************************************************************************/
	/* CDI Bordereaux Format                                                                */
	/****************************************************************************************/

	public static function cdi_prod_remise_format() {
		$format = get_option( 'cdi_o_settings_mondialrelay_OutputPrintingType' );
		return $format;
	}



}

?>
