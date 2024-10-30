<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class cdi_c_Repatriation {

	// Is done once at first start of CDI
	public static function init() {
		$return = null;
		// Copy settings and shipping_method from version < 4.0.0
		$return = null;
		if ( null == get_option( 'cdi_o_transferoldsettings' ) or 'silent' == get_option( 'cdi_o_transferoldsettings' ) ) {
			// Setting collect of old CDI plugin if exist. Only settings. No change to do for cdi DB and for Shippings in WC
			$sets = array(
				'cdi_o_liste_bordereaux_remise' => 'cdi_liste_borderaux_remise',
				'cdi_o_settings_additionalcompensation' => 'wc_settings_tab_colissimo_additionalcompensation',
				'cdi_o_settings_addresswidth' => 'wc_settings_tab_colissimo_addresswidth',
				'cdi_o_settings_amountcompensation' => 'wc_settings_tab_colissimo_amountcompensation',
				'cdi_o_settings_apikeylaposte' => 'wc_settings_tab_colissimo_apikeylaposte',
				'cdi_o_settings_autoclean_gateway' => 'wc_settings_tab_colissimo_autoclean_gateway',
				'cdi_o_settings_autocompleted_intruck' => 'wc_settings_tab_colissimo_autocompleted_intruck',
				'cdi_o_settings_autoparcel_gateway' => 'wc_settings_tab_colissimo_autoparcel_gateway',
				'cdi_o_settings_autoparcel_shippinglist' => 'wc_settings_tab_colissimo_autoparcel_shippinglist',
				'cdi_installation_id' => 'wc_settings_tab_colissimo_cdiplus_ContractNumber',

				'cdi_o_settings_cleanonsuppress' => 'wc_settings_tab_colissimo_cleanonsuppress',
				'cdi_o_settings_cn23_article_description' => 'wc_settings_tab_colissimo_cn23_article_description',
				'cdi_o_settings_cn23_article_hstariffnumber' => 'wc_settings_tab_colissimo_cn23_article_hstariffnumber',
				'cdi_o_settings_cn23_article_origincountry' => 'wc_settings_tab_colissimo_cn23_article_origincountry',
				'cdi_o_settings_cn23_article_quantity' => 'wc_settings_tab_colissimo_cn23_article_quantity',
				'cdi_o_settings_cn23_article_value' => 'wc_settings_tab_colissimo_cn23_article_value',
				'cdi_o_settings_cn23_article_weight' => 'wc_settings_tab_colissimo_cn23_article_weight',
				'cdi_o_settings_cn23_category' => 'wc_settings_tab_colissimo_cn23_category',
				'cdi_o_settings_cn23_eori' => 'wc_settings_tab_colissimo_cn23_eori',
				'cdi_o_settings_Cn23ZipcodeExemptions' => 'wc_settings_tab_colissimo_Cn23ZipcodeExemptions',
				'cdi_o_settings_companyandorderref' => 'wc_settings_tab_colissimo_companyandorderref',

				'cdi_o_settings_colissimo_nochoicereturn_country' => 'wc_settings_tab_colissimo_country_Nochoiceparcelreturn',
				'cdi_o_settings_customcss' => 'wc_settings_tab_colissimo_customcss',
				'cdi_o_settings_defaulttypeparcel' => 'wc_settings_tab_colissimo_defaulttypeparcel',
				'cdi_o_settings_defaulttypereturn' => 'wc_settings_tab_colissimo_defaulttypereturn',
				'cdi_o_settings_merchant_CountryCode' => 'wc_settings_tab_colissimo_departure',

				'cdi_o_settings_exclusiveshippingmethod' => 'wc_settings_tab_colissimo_exclusiveshippingmethod',
				'cdi_o_settings_extentedaddress' => 'wc_settings_tab_colissimo_extentedaddress',
				'cdi_o_settings_extentS1contry' => 'wc_settings_tab_colissimo_extentS1contry',
				'cdi_o_settings_fontsize' => 'wc_settings_tab_colissimo_fontsize',
				'cdi_o_settings_forcedproductcodes' => 'wc_settings_tab_colissimo_forcedproductcodes',

				'cdi_o_settings_getcontentmode' => 'wc_settings_tab_colissimo_getcontentmode',
				'cdi_o_settings_googlemapsapikey' => 'wc_settings_tab_colissimo_googlemapsapikey',
				'cdi_o_settings_colissimo_IncludeCustomsDeclarations' => 'wc_settings_tab_colissimo_IncludeCustomsDeclarations',
				'cdi_o_settings_inserttrackingcode' => 'wc_settings_tab_colissimo_inserttrackingcode',

				'cdi_o_settings_labellayout' => 'wc_settings_tab_colissimo_labellayout',
				'cdi_o_settings_managerank' => 'wc_settings_tab_colissimo_managerank',
				'cdi_o_settings_mapengine' => 'wc_settings_tab_colissimo_mapengine',
				'cdi_o_settings_mapopen' => 'wc_settings_tab_colissimo_mapopen',
				'cdi_o_settings_maprefresh' => 'wc_settings_tab_colissimo_maprefresh',
				'cdi_o_settings_maxitemcn23' => 'wc_settings_tab_colissimo_maxitemcn23',
				'cdi_o_settings_maxitemlogistic' => 'wc_settings_tab_colissimo_maxitemlogistic',
				'cdi_o_settings_methodreferal' => 'wc_settings_tab_colissimo_methodreferal',
				'cdi_o_settings_methodshipping' => 'wc_settings_tab_colissimo_methodshipping',
				'cdi_o_settings_methodshipping_extendtermid' => 'wc_settings_tab_colissimo_methodshipping_extendtermid',
				'cdi_o_settings_methodshippingicon' => 'wc_settings_tab_colissimo_methodshippingicon',
				'cdi_o_settings_miseenpage' => 'wc_settings_tab_colissimo_miseenpage',
				'cdi_o_settings_nbdayparcelreturn' => 'wc_settings_tab_colissimo_nbdayparcelreturn',

				'cdi_o_settings_pagesize' => 'wc_settings_tab_colissimo_pagesize',
				'cdi_o_settings_parcelreference' => 'wc_settings_tab_colissimo_parcelreference',
				'cdi_o_settings_colissimo_parcelreturn' => 'wc_settings_tab_colissimo_parcelreturn',
				'cdi_o_settings_parceltareweight' => 'wc_settings_tab_colissimo_parcelweight',

				'cdi_o_settings_phonemandatory' => 'wc_settings_tab_colissimo_phonemandatory',
				'cdi_o_settings_pickupmethodnames' => 'wc_settings_tab_colissimo_pickupmethodnames',
				'cdi_o_settings_pickupoffline' => 'wc_settings_tab_colissimo_pickupoffline',
				'cdi_o_settings_colissimo_returnparcelservice' => 'wc_settings_tab_colissimo_returnparcelservice',
				'cdi_o_settings_colissimo_returnproduct_code' => 'wc_settings_tab_colissimo_returnproduct_code',
				'cdi_o_settings_rolename_gateway' => 'wc_settings_tab_colissimo_rolename_gateway',

				'cdi_o_settings_selectclickonmap' => 'wc_settings_tab_colissimo_selectclickonmap	',
				'cdi_o_settings_shippingpackageincart' => 'wc_settings_tab_colissimo_shippingpackageincart',
				'cdi_o_settings_signature' => 'wc_settings_tab_colissimo_signature',
				'cdi_o_settings_startrank' => 'wc_settings_tab_colissimo_startrank',
				'cdi_o_settings_testgrid' => 'wc_settings_tab_colissimo_testgrid',
				'cdi_o_settings_colissimo_text_preceding_parcelreturn' => 'wc_settings_tab_colissimo_text_preceding_parcelreturn',
				'cdi_o_settings_colissimo_text_preceding_printreturn' => 'wc_settings_tab_colissimo_text_preceding_printreturn',
				'cdi_o_settings_colissimo_text_preceding_trackingcode' => 'wc_settings_tab_colissimo_text_preceding_trackingcode',
				'cdi_o_settings_trackingemaillocation' => 'wc_settings_tab_colissimo_trackingemaillocation',
				'cdi_o_settings_colissimo_trackingheaders_parcelreturn' => 'wc_settings_tab_colissimo_trackingheaders_parcelreturn',

				'cdi_o_settings_colissimo_url_following_printreturn' => 'wc_settings_tab_colissimo_url_following_printreturn',
				'cdi_o_settings_colissimo_url_trackingcode' => 'wc_settings_tab_colissimo_url_trackingcode',
				'cdi_o_settings_wheremustbeemap' => 'wc_settings_tab_colissimo_wheremustbeemap',

				'cdi_o_settings_colissimo_EuropeCountryCodes' => 'wc_settings_tab_colissimo_ws_EuropeCountryCodes',
				'cdi_o_settings_colissimo_EuropeProductCodes' => 'wc_settings_tab_colissimo_ws_EuropeProductCodes',
				'cdi_o_settings_colissimo_ExceptionProductCodes' => 'wc_settings_tab_colissimo_ws_ExceptionProductCodes',
				'cdi_o_settings_colissimo_FranceCountryCodes' => 'wc_settings_tab_colissimo_ws_FranceCountryCodes',
				'cdi_o_settings_colissimo_FranceProductCodes' => 'wc_settings_tab_colissimo_ws_FranceProductCodes',
				'cdi_o_settings_colissimo_InternationalCountryCodes' => 'wc_settings_tab_colissimo_ws_InternationalCountryCodes',
				'cdi_o_settings_colissimo_InternationalPickupLocationContryCodes' => 'wc_settings_tab_colissimo_ws_InternationalPickupLocationContryCodes',
				'cdi_o_settings_colissimo_InternationalProductCodes' => 'wc_settings_tab_colissimo_ws_InternationalProductCodes',
				'cdi_o_settings_colissimo_InternationalWithoutSignContryCodes' => 'wc_settings_tab_colissimo_ws_InternationalWithoutSignContryCodes',
				'cdi_o_settings_Nocn23ContryCodes' => 'wc_settings_tab_colissimo_ws_Nocn23ContryCodes',
				'cdi_o_settings_colissimo_OffsetDepositDate' => 'wc_settings_tab_colissimo_ws_OffsetDepositDate',
				'cdi_o_settings_colissimo_OutputPrintingType' => 'wc_settings_tab_colissimo_ws_OutputPrintingType',
				'cdi_o_settings_colissimo_OutreMerCountryCodes' => 'wc_settings_tab_colissimo_ws_OutreMerCountryCodes',
				'cdi_o_settings_colissimo_OutreMerProductCodes' => 'wc_settings_tab_colissimo_ws_OutreMerProductCodes',
				'cdi_o_settings_merchant_City' => 'wc_settings_tab_colissimo_ws_sa_City',
				'cdi_o_settings_merchant_CompanyName' => 'wc_settings_tab_colissimo_ws_sa_CompanyName',
				'cdi_o_settings_merchant_CountryCode' => 'wc_settings_tab_colissimo_ws_sa_CountryCode',
				'cdi_o_settings_merchant_Email' => 'wc_settings_tab_colissimo_ws_sa_Email',
				'cdi_o_settings_merchant_Line1' => 'wc_settings_tab_colissimo_ws_sa_Line1',
				'cdi_o_settings_merchant_Line2' => 'wc_settings_tab_colissimo_ws_sa_Line2',
				'cdi_o_settings_merchant_ZipCode' => 'wc_settings_tab_colissimo_ws_sa_ZipCode',
				'cdi_o_settings_colissimo_X' => 'wc_settings_tab_colissimo_ws_X',
				'cdi_o_settings_colissimo_Y' => 'wc_settings_tab_colissimo_ws_Y',

				'cdi_o_settings_encryptioncdistore' => 'wc_settings_tab_colissimo_encryptioncdistore',

				'cdi_installation_id' => 'wc_settings_tab_colissimo_cdiplus_ContractNumber',
				'cdi_o_settings_colissimo_contractnumber' => 'wc_settings_tab_colissimo_ws_ContractNumber',
				'cdi_o_settings_colissimo_password' => 'wc_settings_tab_colissimo_ws_Password',
			);
			$oldcdiexist = false;
			foreach ( $sets as $optdest => $optorig ) {
				$opt = get_option( $optorig );
				if ( $opt ) {
					$oldcdiexist = true;
				}
				update_option( $optdest, $opt );
			}
			// Specials colissimo settings for rapatriation
			update_option( 'cdi_o_settings_colissimo_defautsettings', 'no' ); // To not replace old Colissimo settings with automatics
			update_option( 'cdi_o_settings_forcednocdishipping', '*=colissimo' ); // To force Colissimo carrier for all non-CDI methods

			// Adapt settings to the new structure
			$optstoconvert = array( 'cdi_o_settings_autoparcel_shippinglist', 'cdi_o_settings_pickupmethodnames', 'cdi_o_settings_forcedproductcodes', 'cdi_o_settings_phonemandatory', 'cdi_o_settings_exclusiveshippingmethod' );
			foreach ( $optstoconvert as $opttoconvert ) {
				$toconvert = get_option( $opttoconvert );
				if ( $toconvert ) {
					$toconvert = str_replace( 'colissimo_shippingzone_method_', 'cdi_shipping_colissimo_', $toconvert );
					update_option( $opttoconvert, $toconvert );
				}
			}
			if ( $oldcdiexist and get_option( 'cdi_o_transferoldsettings' ) != 'silent' ) {
				$return = '<div class="notice notice-success is-dismissible"><p></p><p><strong>CDI information : </strong> ' . __( 'You used previously an old CDI plugin version. Its settings have been taken back in this new CDI. Your CDI DB (CDI Gateway) and your CDI shipping parameters are inchanged.', 'cdi' ) . '</p>  <p>' . __( 'If you had a problem starting this new CDI plugin, you can go back to the old plugin. To do this, deactivate this plugin with previously having marked in the CDI settings, general settings, the deletion of data, then reactivate the old plugin.', 'cdi' ) . '</p> <p></p></div>';
				$noticesadmintodisplay = get_option( 'cdi_o_noticesadmintodisplay' );
				$noticesadmintodisplay[] = $return;
				update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
			}
			update_option( 'cdi_o_transferoldsettings', 'done' );
		}
	}
}
