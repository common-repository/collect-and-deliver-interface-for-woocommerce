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
/*
 * Cdi settings in a tab panel added in the woocommerce settings
 */

// CDI official constants
// General
define( 'cdi_o_settings_Nocn23ContryCodes', 'AT,HR,BE,BG,CY,CZ,DK,EE,FI,FR,DE,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,RO,SK,SI,ES,SE' ); // Change in 4.1.11
define( 'cdi_o_settings_Cn23ZipcodeExemptions', 'DE=27498,78266;IT=23030,22060;GR=63086;ES=35001,35002,35003,35004,35005,35006,35007,35008,35009,35010,35011,35012,35013,35014,35015,35016,35017,35018,35019,38001,38002,38003,38004,38005,38006,38007,38008,38009,38010,38107,38108,38110,38160,38320,38617' ); // New in 3.6.0
// Colissimo carrier
define( 'cdi_o_settings_colissimo_nochoicereturn_country', 'US,AU,JP,DE,AT,BE,BG,CY,DK,ES,EE,FI,FR,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,CZ,RO,GB,IE,SK,SI,SE' ); // New in 3.7.13
define( 'cdi_o_settings_colissimo_InternationalWithoutSignContryCodes', 'FR,AD,MC,MQ,GP,RE,GF,YT,PM,MF,BL,BE,CH' ); // Change in 3.5.1
define( 'cdi_o_settings_colissimo_FranceCountryCodes', 'FR,MC,AD' );
define( 'cdi_o_settings_colissimo_FranceProductCodes', 'DOM,DOS,A2P' );
define( 'cdi_o_settings_colissimo_OutreMerCountryCodes', 'MQ,GP,RE,GF,YT,PM,MF,BL,PF,NC,WF,TF' );
define( 'cdi_o_settings_colissimo_OutreMerProductCodes', 'COM,CDS,' );
define( 'cdi_o_settings_colissimo_EuropeCountryCodes', 'DE,AT,BE,BG,CY,HR,DK,ES,EE,FI,GR,HU,IE,IS,IT,LT,LV,LU,MT,NO,NL,PL,PT,CZ,RO,GB,SK,SI,SE,CH' ); // Change in 4.1.11
define( 'cdi_o_settings_colissimo_EuropeProductCodes', 'DOM,DOS,CMT' );
define( 'cdi_o_settings_colissimo_InternationalCountryCodes', '*' );
define( 'cdi_o_settings_colissimo_InternationalProductCodes', 'COLI,COLI,' );
define( 'cdi_o_settings_colissimo_ExceptionProductCodes', 'ACP=BPR,CDI=BPR' );
define( 'cdi_o_settings_colissimo_InternationalPickupLocationContryCodes', 'FR,MC,AD,BE,DE,NL,ES,LU,AT,EE,LV,LT,PL,PT,SE' ); // Change in 3.0.0
define( 'cdi_o_settings_colissimo_trackingheaders_parcelreturn', '6A,9L,6C,9V,6H,6M,8R,7R,8Q,7Q,9W,5R,CP,EY,EN,CM,CA,CB,CI' ); // Change in 3.0.0
define( 'cdi_o_settings_colissimo_returnproduct_code', 'CORE=FR,MC,AD;CORI=DE,AT,BE,ES,FI,IE,IT,LU,NL,PL,CZ,GB,SK,SI,CH,PT,EE,HU,LT,HR,GR,MT,RO,AU' ); // Change in 3.0.0

// Mondial Relay carrier
define( 'cdi_o_settings_mondialrelay_Gp1Codes', 'FR' );
define( 'cdi_o_settings_mondialrelay_Gp1ProductCodes', '24R,24R,24R' );
define( 'cdi_o_settings_mondialrelay_Gp2Codes', 'BE,ES,LU,NL,PT' );
define( 'cdi_o_settings_mondialrelay_Gp2ProductCodes', 'LD1,LD1,24R' );
define( 'cdi_o_settings_mondialrelay_Gp3Codes', 'DE,IT,AT' );
define( 'cdi_o_settings_mondialrelay_Gp3ProductCodes', 'LD1,LD1, ' );

class cdi_c_Settings {
	/**
	 * Bootstraps the class and hooks required actions & filters.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::cdi_admin_enqueue_scripts_setting' );
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::cdi_add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_cdi_tab_settings', __CLASS__ . '::cdi_settings_tab' );
		add_action( 'woocommerce_sections_cdi_tab_settings', __CLASS__ . '::cdi_settings_page' );
		add_action( 'woocommerce_update_options_cdi_tab_settings', __CLASS__ . '::cdi_update_settings' );
		add_action( 'woocommerce_settings_saved', __CLASS__ . '::cdi_woocommerce_settings_saved' );
	}

	public static function cdi_admin_enqueue_scripts_setting( $hook_suffix ) {
		if ( $hook_suffix == 'woocommerce_page_wc-settings' ) {
			wp_enqueue_script( 'cdi_handle_js_admin_setting', plugin_dir_url( __FILE__ ) . '../js/cdiadminsetting.js', array( 'jquery' ), $ver = false, $in_footer = true );
			$varjs = get_option( 'cdi_o_settings_var_js_admin_shipping' );
			wp_add_inline_script( 'cdi_handle_js_admin_setting', $varjs, $position = 'before' );
		}
	}

	public static function cdi_add_settings_tab( $settings_tabs ) {
		$settings_tabs['cdi_tab_settings'] = __( 'CDI', 'cdi' );
		return $settings_tabs;
	}

	public static function cdi_settings_tab() {
		$return = woocommerce_admin_fields( self::get_settings() );
		return $return;
	}

	public static function cdi_update_settings() {
		$return = woocommerce_update_options( self::get_settings() );
		return $return;
	}

	public static function cdi_woocommerce_settings_saved() {
	}

	public static function cdi_settings_page() {
		global $current_section;

		// Set an id installation if not exist in CDI options
		$result = get_option( 'cdi_installation_id' );
		if ( ! $result or $result == '' ) {
			$result = get_option( 'cdi_o_settings_cdiplus_contractnumber' );
			if ( ! $result or $result == '' ) {
				$result = get_option( 'wc_settings_tab_colissimo_cdiplus_ContractNumber' );
				if ( ! $result or $result == '' ) {
					$result = date( 'ymdHis' );
				}
			}
			update_option( 'cdi_installation_id', $result );
		}
		echo '<ul class="subsubsub">';
		$sec = array(
			'section-general'      => 'Réglages Généraux',
			'section-tracking'     => 'Interface Client',
			'section-shipping'     => 'Expéditions',
			'section-printlabel'   => 'Impr. adresses',
			'section-referrals'    => 'Références',
			'section-cn23'         => 'CN23',
			'section-colissimo'    => 'Colissimo',
			'section-mondialrelay' => 'Mondial Relay',
			'section-ups'          => 'UPS',
			'section-collect'      => 'Collect',
		);
		$array_keys = array_keys( $sec );
		foreach ( $sec as $id => $label ) {
			echo wp_kses_post( '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . 'cdi_tab_settings' . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>' );
		}
		echo '</ul><br class="clear" />';
		echo '<p>';
		// To reinstall the official carriers settings
		echo '<em></em><input name="cdi_reset_official_carriers_settings" type="submit" value="' . __( 'Reset the official carriers settings', 'cdi' ) . '" style="float: left; color:red;" title="' . __( 'Warning : To reset your carriers settings (shown with red font)  to official settings if you have inadvertently changed them. Your own website settings (font not in red) will not be modified.', 'cdi' ) . '" /><em></em>';
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_reset_official_carriers_settings'] ) ) {
			update_option( 'cdi_o_settings_colissimo_FranceCountryCodes', cdi_o_settings_colissimo_FranceCountryCodes );
			update_option( 'cdi_o_settings_colissimo_FranceProductCodes', cdi_o_settings_colissimo_FranceProductCodes );
			update_option( 'cdi_o_settings_colissimo_OutreMerCountryCodes', cdi_o_settings_colissimo_OutreMerCountryCodes );
			update_option( 'cdi_o_settings_colissimo_OutreMerProductCodes', cdi_o_settings_colissimo_OutreMerProductCodes );
			update_option( 'cdi_o_settings_colissimo_EuropeCountryCodes', cdi_o_settings_colissimo_EuropeCountryCodes );
			update_option( 'cdi_o_settings_colissimo_EuropeProductCodes', cdi_o_settings_colissimo_EuropeProductCodes );
			update_option( 'cdi_o_settings_colissimo_InternationalCountryCodes', cdi_o_settings_colissimo_InternationalCountryCodes );
			update_option( 'cdi_o_settings_colissimo_InternationalProductCodes', cdi_o_settings_colissimo_InternationalProductCodes );
			update_option( 'cdi_o_settings_colissimo_ExceptionProductCodes', cdi_o_settings_colissimo_ExceptionProductCodes );
			update_option( 'cdi_o_settings_colissimo_InternationalPickupLocationContryCodes', cdi_o_settings_colissimo_InternationalPickupLocationContryCodes );
			update_option( 'cdi_o_settings_colissimo_InternationalWithoutSignContryCodes', cdi_o_settings_colissimo_InternationalWithoutSignContryCodes );
			update_option( 'cdi_o_settings_colissimo_trackingheaders_parcelreturn', cdi_o_settings_colissimo_trackingheaders_parcelreturn );
			update_option( 'cdi_o_settings_colissimo_nochoicereturn_country', cdi_o_settings_colissimo_nochoicereturn_country );
			update_option( 'cdi_o_settings_colissimo_returnproduct_code', cdi_o_settings_colissimo_returnproduct_code );

			update_option( 'cdi_o_settings_mondialrelay_Gp1Codes', cdi_o_settings_mondialrelay_Gp1Codes );
			update_option( 'cdi_o_settings_mondialrelay_Gp1ProductCodes', cdi_o_settings_mondialrelay_Gp1ProductCodes );
			update_option( 'cdi_o_settings_mondialrelay_Gp2Codes', cdi_o_settings_mondialrelay_Gp2Codes );
			update_option( 'cdi_o_settings_mondialrelay_Gp2ProductCodes', cdi_o_settings_mondialrelay_Gp2ProductCodes );
			update_option( 'cdi_o_settings_mondialrelay_Gp3Codes', cdi_o_settings_mondialrelay_Gp3Codes );
			update_option( 'cdi_o_settings_mondialrelay_Gp3ProductCodes', cdi_o_settings_mondialrelay_Gp3ProductCodes );

			update_option( 'cdi_o_settings_Nocn23ContryCodes', cdi_o_settings_Nocn23ContryCodes );
			update_option( 'cdi_o_settings_Cn23ZipcodeExemptions', cdi_o_settings_Cn23ZipcodeExemptions );
		}

		  echo '<em></em><input name="cdi_reinstall_cdi_db" type="submit" value="' . __( 'Reinstall the CDI Gateway', 'cdi' ) . '" style="float: left; color:red;" title="' . __( 'Warning : To reinstall the CDI gateway when something is wrong in CDI process or in an order. The gateway will be recreate empty of parcels. Existing pending parcels in gateway will be deleted, but corresponding orders will be inchanged. All others CDI settings will stay.', 'cdi' ) . '" /><em></em>';
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_reinstall_cdi_db'] ) ) {
			global $wpdb;
			$table = $wpdb->prefix . 'cdi';
			$results = $wpdb->query( "DROP TABLE $table" );
			$structure = "CREATE TABLE IF NOT EXISTS $table (
              id INT(9) NOT NULL AUTO_INCREMENT,
              cdi_order_id VARCHAR(9) NOT NULL,
              cdi_tracking VARCHAR(200) NOT NULL,
              cdi_parcelNumberPartner VARCHAR(200) NOT NULL,
              cdi_hreflabel VARCHAR(200) NOT NULL,
              cdi_status VARCHAR(200) NOT NULL, 
              cdi_reserve VARCHAR(200) NOT NULL,
	      UNIQUE KEY id (id),
              UNIQUE KEY cdi_order_id (cdi_order_id)
            );";
			$wpdb->query( $structure );
		}

		cdi_c_Function::cdi_button_private_message();

		echo '</p><br class="clear">';

		// Init defaut settings and warning if settings has not been saved
		self::cdi_init_options_default( self::get_settings_section_general() );
		self::cdi_init_options_default( self::get_settings_section_cn23() );
		self::cdi_init_options_default( self::get_settings_section_tracking() );
		self::cdi_init_options_default( self::get_settings_section_colissimo() );
		self::cdi_init_options_default( self::get_settings_section_referrals() );
		self::cdi_init_options_default( self::get_settings_section_shipping() );
		self::cdi_init_options_default( self::get_settings_section_mondialrelay() );
		self::cdi_init_options_default( self::get_settings_section_ups() );
		self::cdi_init_options_default( self::get_settings_section_printlabel() );
		self::cdi_init_options_default( self::get_settings_section_collect() );

		$translate = array( __( 'Settings tab section-general', 'cdi' ), __( 'Settings tab section-cn23', 'cdi' ), __( 'Settings tab section-tracking', 'cdi' ), __( 'Settings tab section-colissimo', 'cdi' ), __( 'Settings tab section-referrals', 'cdi' ), __( 'Settings tab section-shipping', 'cdi' ), __( 'Settings tab section-mondialrelay', 'cdi' ), __( 'Settings tab section-ups', 'cdi' ), __( 'Settings tab section-printlabel', 'cdi' ), __( 'Settings tab section-collect', 'cdi' ) );
		$warning = '';
		foreach ( $sec as $key => $s ) {
			$x = get_option( 'cdi_o_settings_' . $key );
			if ( null == get_option( 'cdi_o_settings_' . $key ) ) {
				$warning .= __( 'Settings tab ' . $key, 'cdi' ) . ' | ';
			}
		}
		if ( $warning !== '' ) {
			WC_Admin_Settings::add_message( __( 'It seems yours CDI panels | ', 'cdi' ) . $warning . __( ' have not been registered. So your plugin will not work correctly. Please click on "Register changes" for each of theses panels to have your settings correctly registered.', 'cdi' ) );
		}

		// Warnings if use of old packages
		$wcversion = get_bloginfo( 'version' );
		if ( version_compare( $wcversion, '4.7.0' ) < 0 ) {
			WC_Admin_Settings::add_message( __( 'The Wordpress version is less than 4.7.0. You should upgrade it to fully use CDI', 'cdi' ) );
		}
		$wooversion = cdi_c_Function::cdi_get_woo_version_number();
		if ( version_compare( $wooversion, '3.0.0' ) < 0 ) {
			WC_Admin_Settings::add_message( __( 'The Woocommerce version is less than 3.0.0. You should upgrade it to fully use CDI', 'cdi' ) );
		}
		if ( version_compare( phpversion(), '5.4.0' ) < 0 ) {
			WC_Admin_Settings::add_message( __( 'The PHP version is less than 5.4.0. You should upgrade it to fully use CDI', 'cdi' ) );
		}
		if ( ! extension_loaded( 'openssl' ) ) {
			WC_Admin_Settings::add_message( __( 'The OpenSSL extension is not installed. You should install it to fully use CDI', 'cdi' ) );
		} else {
			$opensslcurrent = cdi_c_Function::get_openssl_version_number( $patch_as_number = true );
			if ( version_compare( $opensslcurrent, '1.0.1' ) < 0 ) {
				WC_Admin_Settings::add_message( __( 'The Openssl version is less than 1.0.1. You should upgrade it to fully use CDI', 'cdi' ) );
			}
		}
		if ( ! ini_get( 'allow_url_fopen' ) && ! function_exists( 'curl_init' ) ) {
			WC_Admin_Settings::add_message( __( 'Your installation has not allow_url_fopen authorized, and moreover Curl is not setup. We can be afraid that CDI will not fully work.', 'cdi' ) );
		}
		if ( ! class_exists( 'SoapClient' ) ) {
			WC_Admin_Settings::add_message( __( 'Your installation has not Soap extension installed. We can be afraid that CDI will not fully work.', 'cdi' ) );
		}
		// Insert Docs
		if ( isset( $_GET['section'] ) ) {
			$section = sanitize_text_field( $_GET['section'] );
		} else {
			$section = 'section-general'; // default section
		}
		switch ( $section ) {
			case 'section-general':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-general.php';
				break;
			case 'section-tracking':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-tracking.php';
				break;
			case 'section-shipping':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-shipping.php';
				break;
			case 'section-printlabel':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-printlabel.php';
				break;
			case 'section-referrals':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-referrals.php';
				break;
			case 'section-cn23':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-cn23.php';
				break;
			case 'section-colissimo':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-colissimo.php';
				break;
			case 'section-mondialrelay':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-mondialrelay.php';
				break;
			case 'section-ups':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-ups.php';
				break;
			case 'section-collect':
				include_once dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Setting-collect.php';
				if ( ! class_exists( 'cdi_c_Collect_Points' ) ) {
					include_once dirname( __FILE__ ) . '/CDI-Carrier-collect/CDI-Collect-Points-Edit.php';
					cdi_c_Collect_Points::cdi_admin_collect_points_edit();
				}
				break;
		}
	}

	public static function cdi_init_options_default( $options ) {
		if ( is_array( $options ) ) {
			foreach ( $options as $option ) {
				if ( isset( $option['id'] ) && ( false === get_option( $option['id'] ) ) && ( get_option( $option['id'], 'null' ) !== null ) ) {
					if ( isset( $option['default'] ) ) {
						$result = $option['default'];
					} else {
						$result = null;
					}
					update_option( $option['id'], $result );
				}
			}
		}
	}

	// Declare of Settings according to the Section called
	public static function get_settings() {
		global $settings;
		if ( isset( $_GET['section'] ) ) {
			$section = sanitize_text_field( $_GET['section'] );
		} else {
			$section = 'section-general'; // default section
		}
		switch ( $section ) {
			case 'section-general':
				$settings = self::get_settings_section_general();
				break;
			case 'section-tracking':
				$settings = self::get_settings_section_tracking();
				break;
			case 'section-shipping':
				$settings = self::get_settings_section_shipping();
				break;
			case 'section-printlabel':
				$settings = self::get_settings_section_printlabel();
				break;
			case 'section-referrals':
				$settings = self::get_settings_section_referrals();
				break;
			case 'section-cn23':
				$settings = self::get_settings_section_cn23();
				break;
			case 'section-colissimo':
				$settings = self::get_settings_section_colissimo();
				break;
			case 'section-mondialrelay':
				$settings = self::get_settings_section_mondialrelay();
				break;
			case 'section-ups':
				$settings = self::get_settings_section_ups();
				break;
			case 'section-collect':
				$settings = self::get_settings_section_collect();
				break;

		}
		return $settings;
	}


	// ref : https://github.com/woothemes/woocommerce/blob/5dcd19f5fa133a25c7e025d7c73e04516bcf90da/includes/admin/class-wc-admin-settings.php#L195
	// Get the settings of Section general
	public static function get_settings_section_general() {
		$selectroles = array();
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		$roles = get_editable_roles();
		foreach ( $GLOBALS['wp_roles']->role_objects as $key => $role ) {
			if ( isset( $roles[ $key ] ) ) {
				$selectroles[ $role->name ] = $role->name;
			}
		}
		$return = array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_general',
			),
			'General section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-general',
			),
			'Type Parcel' => array(
				'name' => __( 'Parcel default settings', 'cdi' ),
				'type' => 'select',
				'options' => array(
					'colis-standard'   => __( 'Standard', 'cdi' ),
					'colis-volumineux' => __( 'Cumbersome', 'cdi' ),
					'colis-rouleau   ' => __( 'Tube', 'cdi' ),
				),
				'default' => 'colis-standard',
				'desc' => __( 'Default Type Parcel', 'cdi' ),
				'id'   => 'cdi_o_settings_defaulttypeparcel',
			),
			'Contre signature' => array(
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Request delivery against signature whenever possible. If not requested, it will be delivery without signature whenever possible. Delivery without signature depends on the carriers and destination countries in which they operate.', 'cdi' ),
				'id'   => 'cdi_o_settings_signature',
			),
			'Additional Compensation' => array(
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Default Additional Compensation :', 'cdi' ),
				'id'   => 'cdi_o_settings_additionalcompensation',
			),
			'Amount Compensation' => array(
				'type' => 'number',
				'default' => '50',
				'desc' => __( 'Default Total Amount Compensation in euros', 'cdi' ),
				'desc_tip' => __( 'Is significant only if additional compensation is checked. It is the default insurance amount in €.', 'cdi' ),
				'id'   => 'cdi_o_settings_amountcompensation',
			),
			'Type Return' => array(
				'type' => 'select',
				'options' => array(
					'no-return'      => __( 'No return', 'cdi' ),
					'pay-for-return' => __( 'Pay for return', 'cdi' ),
				),
				'default' => 'no-return',
				'desc' => __( 'Default Type Return', 'cdi' ),
				'desc_tip' => __( 'Mandatory for some abroad shipments. Consult your carrier support to known which countries need that data.', 'cdi' ),
				'id'   => 'cdi_o_settings_defaulttypereturn',
			),

			'Nb day parcel return' => array(
				'type' => 'number',
				'default' => '+14',
				'desc' => __( 'Number of day after the order creation date+time during whitch a customer parcel return label request is permitted. Setting this number to 0 will allowed customers to use this function only on a case by case basis after the admin has changed this value for the order in the CDI metabox.', 'cdi' ),
				'desc_tip' => __( 'Here, the number of days you allow to your customer to retrieve and print a return parcel label. This number must not be confused which the validity period the carriers give for a label to be deposit. The default 14 days correspond to the European regulation e-commerce for returns if you offer this facility to your customers.', 'cdi' ),
				'id'   => 'cdi_o_settings_nbdayparcelreturn',
			),
			'Clean on suppress' => array(
				'name' => __( 'Plugin features', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Clean CDI datas when plugin is uninstalled', 'cdi' ),
				'desc_tip' => __( 'CDI parameters are normally keep saved in the database when the plugin is uninstalled. Thus, these parameters are operational when the plugin is reinstalled. By cons, when the ckeck is checked, all CDI datas will be cleaned when the plugin is uninstall.', 'cdi' ),
				'id'   => 'cdi_o_settings_cleanonsuppress',
			),
			'Module to log' => array(
				'type' => 'select',
				'options' => array(
					'no debug' => 'no debug',
					'debug' => 'debug',
				),
				'default' => 'debug',
				'desc' => __( 'Select your choice for a debugging log.', 'cdi' ),
				'desc_tip' => __( "Debug traces apply to all CDI modules. They are stored in the wp-content/cdilog.log file and only contain messages about CDI. In addition, CDI can view the error_log and wp-content/debug.log log files when they exist. To activate the wp-content/debug.log file, the configuration of config.php must allow debug mode with: define ('WP_DEBUG', true); define ('WP_DEBUG_LOG', true); define ('WP_DEBUG_DISPLAY', false). To analyze the debugs, you can directly edit the files concerned or use CDI which includes a function for viewing and selecting the 3 debugs files: wp-content/cdilog.log, wp-content/debug.log, and error_log.", 'cdi' ),
				'id'   => 'cdi_o_settings_moduletolog',
			),
			'Role using Gateway' => array(
				'type' => 'multiselect',
				'options' => $selectroles,
				'default' => array( 'shop_manager' ),
				'desc' => __( 'Choose the WP role you want to have access to CDI-Gateway.', 'cdi' ),
				'desc_tip' => __( 'Administrator have already an access to CDI-Gateway. But you can choose to give also access to CDI-Gateway to another role which is not administrator .', 'cdi' ),
				'id'   => 'cdi_o_settings_rolename_gateway',
			),
			'Label and cn23 in shop order list' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Display of icons and pdf files for each order in the shop order list panel', 'cdi' ),
				'id'   => 'cdi_o_settings_display_labelcn23_shoporder',
			),				
		);
		$returnext = array(
			'Encryption cdistore' => array(
				'name' => __( 'Cdistore storage', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Encryption of files when inserted in cdistore for additional security', 'cdi' ),
				'id'   => 'cdi_o_settings_encryptioncdistore',
			),
			'Max days cdistore documents' => array(
				'type' => 'number',
				'default' => '1000',
				'custom_attributes' => array(
					'min' => '1',
					'max' => '1000',
				),
				'desc' => __( 'Number of days to keep documents (label, cn23, bordereau) in the cdistore directory. The recommended number is 30 days to cover any reprints and returns. ', 'cdi' ),
				'desc_tip' => __( 'The documents are deleted as soon as this deadline has passed. The deletion takes place in logistics processing when a new document is stored. Generally the recommended number is 30 days to cover any reprints and returns label to process.', 'cdi' ),
				'id'   => 'cdi_o_settings_maxdaycdistore',
			),
			'Max items logistics documents' => array(
				'type' => 'number',
				'default' => '100',
				'desc' => __( 'Maximum number for logistics documents and for processed items in logistics documents', 'cdi' ),
				'desc_tip' => __( 'The default value is 100 (for parcels selected and for historic logistic documents). Too many could unnecessarily overload the merchant site and the server sites it calls.', 'cdi' ),
				'id'   => 'cdi_o_settings_maxitemlogistic',
			),
			'Parcel reference' => array(
				'name' => __( 'Parcel references', 'cdi' ),
				'type' => 'select',
				'options' => array(
					'orderid'      => __( 'Order Id', 'cdi' ),
					'ordernumber' => __( 'Order Number', 'cdi' ),
				),
				'default' => 'orderid',
				'desc' => __( 'Choice of the reference to apply to package labels: by default "Order Id" which is the standard numbering of Woocommerce orders, or "Order Number" which is a personalization of the numbering of WC orders (by a specialized plugin).', 'cdi' ),
				'id'   => 'cdi_o_settings_parcelreference',
			),
			'Parcel ref in adresse' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Check to add the parcel reference to the name of the company addressed in the parcel label.', 'cdi' ),
				'desc_tip' => __( 'The package reference and the company name in the label can also be configured using CDI filters.', 'cdi' ),
				'id'   => 'cdi_o_settings_companyandorderref',
			),
			'Parcel to gateway' => array(
				'name' => __( 'Automated sequences', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Automatically insert a parcel into the gateway for each order (performed during Woocommerce order list display).', 'cdi' ),
				'id'   => 'cdi_o_settings_autoparcel_gateway',
			),
			'Parcel to gateway Shipping Method list' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'desc' => __( 'Optional when parcel to gateway is selected- Comma separated list of shipping method names for which orders must automatically produce a parcel in gateway. May be a CDI shipping method or external methods. Ex: "cdi_shipping_colissimo_home5, free_shipping". When blank, all shipping methods will be elligible to create a parcel.', 'cdi' ),
				'desc_tip' => __( 'Here, define the shipping methods for which orders will generate a parcel in the gateway, or blank to select all shipping methods', 'cdi' ),
				'id'   => 'cdi_o_settings_autoparcel_shippinglist',
			),
			'Nettoyage automatique' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Auto clean of parcels in gateway when "In truck" status', 'cdi' ),
				'id'   => 'cdi_o_settings_autoclean_gateway',
			),
			'Order completed when parcel intruck' => array(
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Automatically pass orders in "completed" status when parcel is in "intruck" status (performed during Woocommerce order list display).', 'cdi' ),
				'id'   => 'cdi_o_settings_autocompleted_intruck',
			),
			'Sender Address - Company Name' => array(
				'name' => __( 'e-Merchant Address', 'cdi' ),
				'type' => 'text',
				'default' => 'The CDI Company',
				'desc' => __( 'e-Merchant Address - Company Name (May be same as website name)', 'cdi' ),
				'desc_tip' => __( 'The 6 following datas define your address as merchant. This address will be use by carriers, on parcels, and on logistic documents.', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_CompanyName',
			),
			'Sender Address - Line 1' => array(
				'type' => 'text',
				'default' => 'Mandatory - num and street',
				'desc' => __( 'e-Merchant Address - Mandatory num and street', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_Line1',
			),
			'Sender Address - Line 2' => array(
				'type' => 'text',
				'default' => 'Optionnal - other infos',
				'desc' => __( 'e-Merchant Address - Optionnal other infos', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_Line2',
			),
			'Sender Address - ZipCode' => array(
				'type' => 'text',
				'default' => '75001',
				'desc' => __( 'e-Merchant Address - ZipCode', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_ZipCode',
			),
			'Sender Address - City' => array(
				'type' => 'text',
				'default' => 'PARIS',
				'desc' => __( 'e-Merchant Address - City', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_City',
			),
			'Sender Address - Country Code' => array(
				'type' => 'single_select_country',
				'default' => 'FR',
				'desc' => __( 'e-Merchant Address - Country Code', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_CountryCode',
			),
			'Sender Address - Fix Phone' => array(
				'type' => 'text',
				'default' => '0123456789',
				'desc_tip' => __( 'For some carriers, fix phone is mandatory.', 'cdi' ),
				'desc' => __( 'e-Merchant Fix Phone', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_fixphone',
			),
			'Sender Address - Cellular Phone' => array(
				'type' => 'text',
				'default' => '0623456789',
				'desc_tip' => __( 'For some carriers, cellular phone is mandatory.', 'cdi' ),
				'desc' => __( 'e-Merchant Cellular Phone', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_cellularphone',
			),
			'Sender Address - Email' => array(
				'type' => 'email',
				'default' => 'a@b.fr',
				'desc' => __( 'e-Merchant Address - Email', 'cdi' ),
				'desc_tip' => __( 'Your email address as merchant.', 'cdi' ),
				'id'   => 'cdi_o_settings_merchant_Email',
			),
		);
		$return = array_merge( $return, $returnext );
		return array_merge(
			$return,
			array(
				/*
				'Installation Id' => array(
				'type' => 'text',
				'custom_attributes' => array (
				 'pattern' => '[0-9]{12,12}',
				 ),
				'default' => '',
				'desc' => __( 'Here is the installation id - Mandatory for some carriers and for some logistic documents - CDI fill it with a time stamp','cdi' ),
				'id'   => 'cdi_installation_id'
				), */
				'section_end' => array(
					'type' => 'sectionend',
					'id' => 'cdi_o_settings_section_general_end',
				),
			)
		);
	}

	// Get the settings of Section tracking
	public static function get_settings_section_tracking() {
		$return = array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_tracking',
			),
			'Tracking section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-tracking',
			),
			'Tracking code to customer' => array(
				'name' => __( 'Tracking code to customer', 'cdi' ),
				'type' => 'select',
				'options' => array(
					'no'                     => __( 'nothing', 'cdi' ),
					'emails'                 => __( 'emails', 'cdi' ),
					'order-views'            => __( 'order-views', 'cdi' ),
					'emails and order-views' => __( 'emails and order-views', 'cdi' ),
				),
				'default' => 'emails and order-views',
				'desc' => __( 'Insert tracking code and pickup location in Customer emails and/or Customer order views', 'cdi' ),
				'desc_tip' => __( 'You can choose to insert customer informations in the customer  mails and / or the customer order views. Information shown are the tracking code and the pickup location the customer has choosen.', 'cdi' ),
				'id'   => 'cdi_o_settings_inserttrackingcode',
			),
		);
		$returnext = array(
			'Tracking email location' => array(
				'type' => 'select',
				'options' => array(
					'after'       => __( 'After the order details', 'cdi' ),
					'before'      => __( 'Before the order details', 'cdi' ),
				),
				'default' => 'after',
				'desc' => __( 'Location in emails where the tracking infos must be', 'cdi' ),
				'desc_tip' => __( "You can choose the location in emails where the tracking infos must be. The defaut is 'after' the order details because it is more consistant in display. The choice 'before'  is to be immediatly seen by the consumer.", 'cdi' ),
				'id'   => 'cdi_o_settings_trackingemaillocation',
			),
			'Add S1 Hub Armees country' => array(
				'name' => __( 'Extend WC countries list with "S1 - Envoi vers les Armées" country', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Extend the WC countries list with "S1 - Envoi vers les Armées" country. This S1 country must be refered to in a WC internationnal shipping zone. It must be used according to LaPoste specifications for army hub.', 'cdi' ),
				'id'   => 'cdi_o_settings_extentS1contry',
			),
			'Extented WC address' => array(
				'name' => __( 'Customer address structure', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Extend input customer addresses to 4 lines according to the international S42 address standard', 'cdi' ) . ' ' . __('(Checkout Block is not compatible with this option)', 'cdi'),
				'id'   => 'cdi_o_settings_extentedaddress',
			),
			'Line address max size' => array(
				'type' => 'select',
				'options' => array(
					'32'       => __( '32 digits', 'cdi' ),
					'35'       => __( '35 digits', 'cdi' ),
					'38'       => __( '38 digits', 'cdi' ),
					'no'      => __( 'no limit', 'cdi' ),
				),
				'default' => '32',
				'desc' => __( 'Maximum length of customer address lines (32, 35, 38, or unlimited like WC). 32 is recommended.', 'cdi' ),
				'desc_tip' => __( "Carriers use different standards for their destination address lines. To facilitate the interchangability of carriers, it is recommended to choose the length of 32 characters. The sizes corresponding to the different standards adopted by the carriers. There remains the possibility of personalizing the addresses by the CDI filter 'cdi_filterarray_auto_arrayforcarrier' preceding each call to the label web service of each carrier. ", 'cdi' ),
				'id'   => 'cdi_o_settings_maxsizeaddressline',
			),
			'Add Pickup address' => array(
				'name' => __( 'Add full pickup address in view order and email', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Add full pickup address in view order and email. Concomitamment there is a clarification of WC shipping address translation (shipping becoming destination).', 'cdi' ),
				'id'   => 'cdi_o_settings_pickupaddresslayout',
			),
			'La Poste ControlAdresse API key' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => '',
				'desc' => __( 'La Poste ControlAdresse API key. Key to get at https://developer.laposte.fr/', 'cdi' ),
				'desc_tip' => __( 'Here, the La Poste ControlAdresse API key to be authorize to ask address control.', 'cdi' ),
				'id'   => 'cdi_o_settings_apikeylaposte',
			),
		);
		$return = array_merge( $return, $returnext );
		return array_merge(
			$return,
			array(
				'section_end' => array(
					'type' => 'sectionend',
					'id' => 'cdi_o_settings_section_tracking_end',
				),
			)
		);
	}

	// Get the settings of Section shipping
	public static function get_settings_section_shipping() {
		$return = array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_shipping',
			),
			'Shipping section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-shipping',
			),
			'Shipping Method' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Global enabling of CDI shipping method (Shipping zone mode)', 'cdi' ),
				'desc_tip' => __( 'You must have installed WooCommerce 2.6 or further to run this shipping method.  Details of settings must be done in each methods [WooCommerce -> Settings -> Shipping].', 'cdi' ),
				'id'   => 'cdi_o_settings_methodshipping',
			),
			'Parcel empty weight' => array(
				'type' => 'number',
				'default' => '250',
				'desc' => __( 'Default weight (in grams) of empty package (net weight of products will be added)', 'cdi' ),
				'desc_tip' => __( 'If products have not weight, this tare weight will be the default weight of the parcel. Anyway, the total computed weight of the parcel can be change manually in the order meta box before processing the parcel.', 'cdi' ),
				'id'   => 'cdi_o_settings_parceltareweight',
			),
			'Icon Shipping Method' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Set shipping method icons in front end.', 'cdi' ),
				'desc_tip' => __( 'Customization of icons are to do in plugin images directory (refer to examples for size and type) or trought a filter to keep the images in your own directory.', 'cdi' ),
				'id'   => 'cdi_o_settings_methodshippingicon',
			),
		);
		$returnext = array(
			'Shipping package-in-cart' => array(
				'type' => 'select',
				'options' => array(
					'first' => __( 'First shipping package', 'cdi' ),
					'last' => __( 'Last shipping package', 'cdi' ),
					'cart' => __( 'Whole cart', 'cdi' ),
				),
				'default' => 'first',
				'desc' => __( 'If multi shipping packages, select what CDI Gateway must process.', 'cdi' ),
				'desc_tip' => __( 'If WC multi shipping packages (e.g. a market places activated), you can choose which package is to be process by the CDI Gateway : first package, last package, or whole cart. The defaut is first package.', 'cdi' ),
				'id'   => 'cdi_o_settings_shippingpackageincart',
			),
			'Notcdi label name' => array(
				'type' => 'text',
				'default' => 'Custom',
				'desc' => __( 'Defines the name to assign to the "notcdi" CDi shipping method. The default name is "Custom" ; Internal name is "notcdi".', 'cdi' ),
				'desc_tip' => __( 'Here, defines the name to assign to the "notcdi" CDi shipping method. The default name is "Custom" ; Internal name is "notcdi"', 'cdi' ),
				'id'   => 'cdi_o_settings_notcdi_labelname',
			),
			'Extend Termid Shipping Method' => array(
				'name' => __( 'Extend Termid list', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:70%;',
				'desc' => __( 'Optional - Comma separated list of shipping method Termid which will be added to the standard list. Ex: "home6, home7, shop6, autre1, autre2"', 'cdi' ),
				'desc_tip' => __( 'Here, extend the shipping methods Termid for your special customization.', 'cdi' ),
				'id'   => 'cdi_o_settings_methodshipping_extendtermid',
			),				
		);
		$return = array_merge( $return, $returnext );
		return array_merge(
			$return,
			array(
				'section_end' => array(
					'type' => 'sectionend',
					'id' => 'cdi_o_settings_section_shipping_end',
				),
			)
		);
	}

	// Get the settings of Section printlabel
	public static function get_settings_section_printlabel() {
		return array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_printlabel',
			),
			'Printlabel section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-printlabel',
			),
			'Page size' => array(
				'type' => 'text',
				'default' => '210x297',
				'desc' => __( 'Page size width x height in mm', 'cdi' ),
				'desc_tip' => __( 'Here the size of the page in your printer. Syntax = width x height in mm (ex 210x297 for a portrait A4 format).', 'cdi' ),
				'id'   => 'cdi_o_settings_pagesize',
			),
			'Labels layout' => array(
				'type' => 'text',
				'default' => '2x4',
				'desc' => __( 'Layout of labels in the page: width  x height', 'cdi' ),
				'desc_tip' => __( 'Here, the layout of the labels in the page: nb-in-width x nb-in-height (ex 3x5 which will give a total of 15 labels in the page).', 'cdi' ),
				'id'   => 'cdi_o_settings_labellayout',
			),
			'Address show size' => array(
				'type' => 'text',
				'default' => '85%',
				'desc' => __( 'Width of the viewing area of the address: in %', 'cdi' ),
				'desc_tip' => __( 'Here, the width of the display area of the address on the label, expressed in % of the width of the label (ex 85%).', 'cdi' ),
				'id'   => 'cdi_o_settings_addresswidth',
			),
			'Font size' => array(
				'type' => 'text',
				'default' => '14px',
				'desc' => __( 'Address font size : in css unit', 'cdi' ),
				'desc_tip' => __( 'Here, the address font size in css unit (ex 12px).', 'cdi' ),
				'id'   => 'cdi_o_settings_fontsize',
			),
			'Start rank' => array(
				'type' => 'text',
				'default' => '1',
				'custom_attributes' => array(
					'pattern' => '[1-9][0-9]{0,1}',
					'required' => 'yes',
				),
				'desc' => __( 'Position of the first label to be printed.', 'cdi' ),
				'desc_tip' => __( 'Here, the position of the first label to be printed. This number must be between 1 and the total number of labels in the page.', 'cdi' ),
				'id'   => 'cdi_o_settings_startrank',
			),
			'Start rank manage' => array(
				'type' => 'select',
				'default' => 'forward',
				'options' => array(
					'fix'     => 'fix',
					'forward' => 'forward',
				),
				'desc' => __( 'Mode for managing the position of the first label', 'cdi' ),
				'desc_tip' => __( 'Here, the mode of management of the position of the 1st label. fix to always start at the same location in the pages; forward to automatically advance the position and thus allow to use all the labels of the pages.', 'cdi' ),
				'id'   => 'cdi_o_settings_managerank',
			),
			'Test grid' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Test grid mode to help the setting of the printer', 'cdi' ),
				'desc_tip' => __( 'Here, the test grid mode is used to assist in the setting of the printer. In this mode, the positions of the labels are delimited by a grid.', 'cdi' ),
				'id'   => 'cdi_o_settings_testgrid',
			),
			'Css page layout' => array(
				'type' => 'textarea',
				'css'  => 'width:70%; height:4em;',
				'default' => 'padding: 0 !important; margin-top: 5mm !important; height:96% !important; margin-left: 12mm !important; width:99% !important;',
				'desc' => __( 'Area specifying the layout in the sheet, in css code', 'cdi' ),
				'desc_tip' => __( 'Here, the zone specifying the layout in the sheet, in css code (Ex: padding: 0 !important; margin-top: 5mm !important; height:96% !important; margin-left: 12mm !important; width:99% !important;).', 'cdi' ),
				'id'   => 'cdi_o_settings_miseenpage',
			),
			'Custom css' => array(
				'type' => 'textarea',
				'css'  => 'width:70%; height:10em;',
				'default' => '.address_show {font-weight: bold;}',
				'desc' => __( 'Zone specifying formatting of addresses, in css  code', 'cdi' ),
				'desc_tip' => __( 'Here, the area specifying the formatting of the addresses, in css code.', 'cdi' ),
				'id'   => 'cdi_o_settings_customcss',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'cdi_o_settings_section_printlabel_end',
			),
		);
	}

	// Get the settings of Section referrals
	public static function get_settings_section_referrals() {
		  $return = array(
			  'section_title' => array(
				  'name'     => ' ',
				  'type'     => 'title',
				  'desc'     => '',
				  'id'       => 'cdi_o_settings_section_referrals',
			  ),
			  'Referrals section mark' => array(
				  'css'  => 'display:none;',
				  'type' => 'number',
				  'default' => '1',
				  'id'   => 'cdi_o_settings_section-referrals',
			  ),
			  'Method Referal' => array(
				  'type' => 'checkbox',
				  'default' => 'yes',
				  'desc' => __( 'Global enable of referrals to shipping method.', 'cdi' ),
				  'desc_tip' => __( 'Shipping method referrals enables to set qualifications to shipping methods presented to customer : - shipping methods for which the Pickup locations function must be activate, - the carriers product codes to associate to shipping methods, - shipping methods which are exclusive.', 'cdi' ),
				  'id'   => 'cdi_o_settings_methodreferal',
			  ),
			  'pickup_method_name' => array(
				  'name' => __( 'Pickup locations', 'cdi' ),
				  'type' => 'textarea',
				  'css'  => 'width:70%; height:4em;',
				  'default' => 'cdi_shipping_colissimo_pick1, cdi_shipping_colissimo_pick2 ,cdi_shipping_mondialrelay_pick1, cdi_shipping_mondialrelay_pick2, cdi_shipping_ups_pick1, cdi_shipping_ups_pick2',
				  'desc_tip' => __( 'Optional - Comma separated list of "Shipping-Method-names = filter-relay" which activate the CDI pickup location choice process. Filter-relay = 0 or 1 to define type of list according to the carrier. May be methods of CDI shipping or external methods. Selection may focus on a specific instance as "flat_rate:25=1" ', 'cdi' ),
				  'id'   => 'cdi_o_settings_pickupmethodnames',
			  ),
			  'Pickup excluded when offline' => array(
				  'type' => 'checkbox',
				  'default' => 'yes',
				  'desc' => __( 'Not show pickup tariffs when offline (no access to outgoing IP, LaPoste server, ... ).', 'cdi' ),
				  'id'   => 'cdi_o_settings_pickupoffline',
			  ),
			  'Pickup location map open' => array(
				  'type' => 'checkbox',
				  'default' => 'yes',
				  'desc' => __( 'Pickup location map shown open at entry of selected method.', 'cdi' ),
				  'id'   => 'cdi_o_settings_mapopen',
			  ),
			  'Pickup location mode selectclickonmap' => array(
				  'type' => 'checkbox',
				  'default' => 'yes',
				  'desc' => __( 'Pickup location mode to select location by click on map. Warning, this option may not work with some themes, plugins, and/or browser.', 'cdi' ),
				  'id'   => 'cdi_o_settings_selectclickonmap',
			  ),
			  'Pickup location mode wheremustbeemap' => array(
				  'type' => 'select',
				  'options' => array(
					  'insertBefore( ".shop_table" )' => __( 'Before shop box', 'cdi' ),
					  'insertAfter( ".shop_table" )'  => __( 'After shop box', 'cdi' ),
					  'insertBefore( "#payment" )' => __( 'Before payment', 'cdi' ),
					  'insertAfter( "#payment" )'  => __( 'After payment', 'cdi' ),
					  'insertBefore( "#order_review" )' => __( 'Before order review', 'cdi' ),
					  'insertAfter( "#order_review" )'  => __( 'After order review', 'cdi' ),

				  ),
				  'default' => 'insertBefore( ".shop_table" )',
				  'desc' => __( 'Pickup location to choose where must be map in checkout page. Warning, this option may not work with some themes, plugins, and/or browser.', 'cdi' ),
				  'id'   => 'cdi_o_settings_wheremustbeemap',
			  ),
			  'Pickup location map engine' => array(
				  'type' => 'select',
				  'options' => array(
					  'gm' => __( 'Google Maps', 'cdi' ),
					  'om'  => __( 'Open Map', 'cdi' ),
				  ),
				  'default' => 'om',
				  'desc' => __( 'Pickup location map engine to choose. Google Maps needs an API key that you must place in field below. The alternative called Open Map is build with 3 services : Open Layers, Open Street Map, and Nominatim, which are open and free.', 'cdi' ),
				  'id'   => 'cdi_o_settings_mapengine',
			  ),
			  'googlemap api key' => array(
				  'type' => 'text',
				  'css'  => 'width:70%;',
				  'default' => '',
				  'desc' => __( 'Optional - Google maps API key', 'cdi' ),
				  'desc_tip' => __( 'Here, set your Google maps API key. Without a key, you will have a restricted use of Google maps.', 'cdi' ),
				  'id'   => 'cdi_o_settings_googlemapsapikey',
			  ),
			  'forced_non-cdi shipping' => array(
				  'name' => __( 'Non CDI shipping', 'cdi' ),
				  'type' => 'text',
				  'css'  => 'width:70%;',
				  'default' => '*=colissimo',
				  'desc_tip' => __( 'Optional - Only for non-CDI shipping methods. Forces a carrier for an external delivery method in CDI processing. For example: flat_rate:25=colissimo,ups_external_method=ups.  An "*=carrier" means that this carrier is forced for all non-CDI shipping methods. ', 'cdi' ),
				  'desc' => __( 'Optional - Only if you have non-CDI shipping methods.', 'cdi' ),
				  'id'   => 'cdi_o_settings_forcednocdishipping',
			  ),
			  'forced_product_code' => array(
				  'name' => __( 'Associated product codes', 'cdi' ),
				  'type' => 'textarea',
				  'css'  => 'width:70%; height:5em;',
				  'default' => 'cdi_shipping_colissimo_home1=DOM, cdi_shipping_colissimo_home2= DOS,cdi_shipping_mondialrelay_home1=LD1, cdi_shipping_mondialrelay_home2=LD2, cdi_shipping_mondialrelay_pick1=24R, cdi_shipping_ups_home1=11, cdi_shipping_ups_home2=07, cdi_shipping_ups_pick1=AP',
				  'desc_tip' => __( 'Optional - List of comma separated relations "Method-name = carrier-product-code" to be use. May be methods of CDI shipping or external methods.', 'cdi' ),
				  'id'   => 'cdi_o_settings_forcedproductcodes',
			  ),
			  'mandatory phone number' => array(
				  'name' => __( 'Mandatory phone number', 'cdi' ),
				  'type' => 'textarea',
				  'css'  => 'width:70%; height:5em;',
				  'default' => 'cdi_shipping_colissimo_pick1, cdi_shipping_colissimo_pick2, cdi_shipping_mondialrelay_pick1, cdi_shipping_mondialrelay_pick2, cdi_shipping_ups_pick1, cdi_shipping_ups_pick2',
				  'desc_tip' => __( 'Optional - Form a comma separated list of "Method-name" to be use for mandatory phone number. May be methods of CDI shipping or external methods. An "*" means that phone number is mandatory for all shipping methods.', 'cdi' ),
				  'id'   => 'cdi_o_settings_phonemandatory',
			  ),
			  'Exclusive Shipping Method' => array(
				  'name' => __( 'Exclusive shipping methods', 'cdi' ),
				  'type' => 'text',
				  'css'  => 'width:70%;',
				  'desc' => __( 'Optional - Comma separated list of shipping method names which are exclusive of others. The priority is given to the first method matching in the original woocommerce package list. May be methods of CDI shipping or external methods. Ex: "cdi_shipping_mondialrelay_pick1, free_shipping"', 'cdi' ),
				  'desc_tip' => __( 'Here, define the shipping methods which are exclusive (i.e. which will be alone when presented to customer).', 'cdi' ),
				  'id'   => 'cdi_o_settings_exclusiveshippingmethod',
			  ),
		  );
		  $returnext = array(
			  'section_end' => array(
				  'type' => 'sectionend',
				  'id' => 'cdi_o_settings_section_referrals_end',
			  ),
		  );
		  $return = array_merge( $return, $returnext );
		  return $return;
	}

	// Get the settings of Section cn23
	public static function get_settings_section_cn23() {
		return array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_cn23',
			),
			'CN23 section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-cn23',
			),
			'Global shipment Description' => array(
				'name' => __( 'Shipment desciption :', 'cdi' ),
				'type' => 'text',
				'default' => 'Ex: Vêtements confection',
				'desc' => __( 'General description of the package. It is required by some carriers in all shipping situations, in addition to the CN23 descriptions.', 'cdi' ),
				'desc_tip' => __( "General description of the package. It is required by some carriers in all shipping situations, in addition to the CN23 descriptions. For example: 'Ready-made clothing'.", 'cdi' ),
				'id'   => 'cdi_o_settings_global_shipment_description',
			),
			'CN23 default' => array(
				'name' => __( 'CN23 :', 'cdi' ),
				'type' => 'select',
				'options' => array(
					'1' => __( 'Gift', 'cdi' ),
					'2' => __( 'Sample', 'cdi' ),
					'3' => __( 'Commercial', 'cdi' ),
					'4' => __( 'Documents', 'cdi' ),
					'5' => __( 'Other', 'cdi' ),
					'6' => __( 'Returned goods', 'cdi' ),
				),
				'default' => '3',
				'desc' => __( 'Default CN23 Category', 'cdi' ),
				'desc_tip' => __( 'Give the nature of the contents of the package in CN23 codification. CN23 is an internationally recognized standard, to codify customs declarations. In France, a CN23 is required for all shipments abroad and the TOM-DOM except: DE, AT, BE, BG, CY, DK ES  EE, FI, E, GR, HU, IE, IT, LV, LT, LU, MT, NL,  PL , PT , CZ , RO , GB , IE , SK , SI , SE.', 'cdi' ),
				'id'   => 'cdi_o_settings_cn23_category',
			),
			'CN23 Article Description' => array(
				'type' => 'text',
				'desc' => __( 'Default CN23 Description of article (blank = copied from product order)', 'cdi' ),
				'desc_tip' => __(
					'Give the the default description of articles in the parcel. Up to 10 items/products can be included in a CDI CN23.
If null or 0,  product title, product weight, product quantity, and product price in the order will be considered. ',
					'cdi'
				),
				'id'   => 'cdi_o_settings_cn23_article_description',
			),
			'CN23 Article Weight' => array(
				'type' => 'number',
				'default' => 0,
				'desc' => __( 'Default CN23 Net weight in grams of one article (0 = copied from product order)', 'cdi' ),
				'id'   => 'cdi_o_settings_cn23_article_weight',
			),
			'CN23 Article Quantity' => array(
				'type' => 'number',
				'default' => 0,
				'desc' => __( 'Default CN23 Number of articles in the parcel (0 = copied from product order)', 'cdi' ),
				'id'   => 'cdi_o_settings_cn23_article_quantity',
			),
			'CN23 Article Value' => array(
				'type' => 'number',
				'default' => 0,
				'desc' => __( 'Default CN23 ex VAT Value in € of one article (0 = copied from product order)', 'cdi' ),
				'id'   => 'cdi_o_settings_cn23_article_value',
			),
			'CN23 Article HStariffnumber' => array(
				'type' => 'text',
				'default' => '620630',
				'custom_attributes' => array(
					'pattern' => '[0-9]{4,10}',
				),
				'desc' => __( 'Default CN23 HS tariff code- 4 to 10 digits (only if "Commercial" category)', 'cdi' ) . ' - <a href="https://www.douane.gouv.fr/demarche/connaitre-la-nomenclature-de-votre-marchandise" target="_blank">HS Tariff code</a>',
				'desc_tip' => __( 'HS Tariff is an internationally recognized standard, to codify customs declarations. This code is required only for commercial shipment', 'cdi' ),
				'id'   => 'cdi_o_settings_cn23_article_hstariffnumber',
			),
			'CN23 Article OriginCountry' => array(
				'type' => 'single_select_country',
				'default' => 'FR',
				'desc' => __( 'Default CN23 ISO code of origine country (for Customs)', 'cdi' ),
				'desc_tip' => __( 'Required by some customs : the 2 letters ISO code of origine country of the item', 'cdi' ),
				'id'   => 'cdi_o_settings_cn23_article_origincountry',
			),
			'Max items cn23' => array(
				'type' => 'number',
				'default' => '100',
				'desc' => __( 'Maximum number for cn3 articles in metabox', 'cdi' ),
				'desc_tip' => __( 'The default value is 100. You must choose a raisonnable value, lower than 100 if possible. Too many could unnecessarily overload the merchant site at any change inside metabox.', 'cdi' ),
				'id'   => 'cdi_o_settings_maxitemcn23',
			),
			'CN23 EORI' => array(
				'name' => __( 'Sender EORI code', 'cdi' ),
				'type' => 'text',
				'default' => '',
				'custom_attributes' => array(
					'pattern' => '^FR[0-9]{14,14}',
				),
				'desc' => __( 'Optionnal : Sender EORI (Economic Operator Registration and Identification) to be marked in cn23 according to some regulations', 'cdi' ),
				'desc_tip' => __( 'EORI : European regulations provide a unique Community identifier number for use by international economic operators.', 'cdi' ),
				'id'   => 'cdi_o_settings_cn23_eori',
			),
			'EU countries without cn23' => array(
				'name' => __( 'EU Countries exempted', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:70%; color:red;',
				'default' => cdi_o_settings_Nocn23ContryCodes,
				'desc' => __( 'Comma separated list of EU country codes exempted of cn23 documents. Be aware that this list is from the European rules.', 'cdi' ),
				'desc_tip' => __( 'Here, is the list of EU country codes exempted of cn23 documents. Be aware that this list is from the European rules.', 'cdi' ),
				'id'   => 'cdi_o_settings_Nocn23ContryCodes',
			),
			'Cn23 zipcode exemptions' => array(
				'name' => __( 'EU zipcode exemptions', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:70%; color:red;',
				'default' => cdi_o_settings_Cn23ZipcodeExemptions,
				'desc' => __( 'Semicolon separated list of relations "country-code=list of zip-code" for which some territories, imbedded in a EU state, do not get the EU cn23 exemption. This list is useful only for the territories without an ISO country code. Be aware that this list is from the European rules.', 'cdi' ),
				'desc_tip' => __( 'Here, semicolon separated list of relations "country-code=list of zip-code" for which some territories, imbedded in a EU state, do not get the EU cn23 exemption. This list is useful only for the territories without an ISO country code. Be aware that this list is from the European rules.', 'cdi' ),
				'id'   => 'cdi_o_settings_Cn23ZipcodeExemptions',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'cdi_o_settings_section_cn23_end',
			),
		);
	}

	// Get the settings of Section Colissimo
	public static function get_settings_section_colissimo() {
		$route = 'cdi_c_Carrier_' . 'colissimo' . '::cdi_carrier_update_settings';
		( $route )();
		update_option(
			'cdi_o_settings_section-colissimo-warning',
			__(
				'Warning  :
- To use the Colissimo carrier, you must sign a business contract with it including Web services: Postage, Choice of deliveries, Tracking.
- Colissimo does not provide a test account for the integration of your franking services, choice of collection points, parcel tracking, return labels. On the other hand, your franking labels produced will not be invoiced by Colissimo as long as you do not drop them off for shipment.
- For all questions relating to the operation of Colissimo services, you must contact your Colissimo sales representative or Colissimo technical support.',
				'cdi'
			)
		);
		return array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_colissimo',
			),
			'Colissimo section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-colissimo',
			),
			'Contract Number' => array(
				'name' => __( 'Contract :', 'cdi' ),
				'type' => 'text',
				'default' => '',
				'desc' => __( 'Web Service - Contract Number', 'cdi' ),
				'desc_tip' => __( "Your Colissimo-LaPoste contrat number including the option 'Web Service (Flexibilité) pour livraison et étiquetage'.", 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_contractnumber',
			),
			'Password' => array(
				'type' => 'text',
				'default' => '',
				'desc' => __( 'Web Service - Password', 'cdi' ),
				'desc_tip' => __( 'Your password at your Colissimo-LaPoste contrat', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_password',
			),
			'Colissimo section warning' => array(
				'type' => 'textarea',
				'css'  => 'width:70%; height:5em; color:blue;',
				'default' => '',
				'id'   => 'cdi_o_settings_section-colissimo-warning',
			),
			'Colissimo defaut settings' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Add in referrals settings the Colissimo default for shipping méthods: pickup , product code, mandatory phone..', 'cdi' ),
				'desc_tip' => __( 'Will be added at the end of referrals settings - as pickup : cdi_shipping_colissimo_pick1, cdi_shipping_colissimo_pick2 ; as product code : cdi_shipping_colissimo_home1=DOM, cdi_shipping_colissimo_home2=DOS ; as mandatory phone : cdi_shipping_colissimo_pick1, cdi_shipping_colissimo_pick2 .', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_defautsettings',
			),
			'Output Format - Offset X' => array(
				'name' => __( 'Label layout :', 'cdi' ),
				'type' => 'number',
				'default' => 0,
				'desc' => __( 'Web Service - Output Format - Offset X in pixels', 'cdi' ),
				'desc_tip' => __( 'The 3 following datas are Colissimo settings for the printing.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_X',
			),
			'Output Format - Offset Y' => array(
				'type' => 'number',
				'default' => 0,
				'desc' => __( 'Web Service - Output Format - Offset Y in pixels', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_Y',
			),
			'Output Format - Printing Type' => array(
				'type' => 'select',
				'options' => array(
					'PDF_10x15_300dpi' => 'PDF 10x15',
					'PDF_A4_300dpi' => 'PDF A4 Paysage',
				),
				'default' => 'PDF_A4_300dpi',
				'desc' => __( 'Web Service - Output Format - Printing Type (DPL & ZPL not supported)', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_OutputPrintingType',
			),
			'Offset Deposit Date' => array(
				'type' => 'number',
				'default' => '2',
				'custom_attributes' => array(
					'min' => '1',
					'max' => '10',
				),
				'desc' => __( 'Web Service - Offset Deposit Date (estimate in days after running the web service - Must be > 0)', 'cdi' ),
				'desc_tip' => __( 'Estimate period in days to deposit yours parcels. Useful for La Poste.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_OffsetDepositDate',
			),
			'return CN23 labels' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Include CN23 customs declarations with returned labels', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_IncludeCustomsDeclarations',
			),

			'Text preceding tracking code' => array(
				'name' => __( 'Tracking information for customer:', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'Order shipped. Your tracking code is : ', 'cdi' ),
				'desc' => __( 'Text preceding tracking code', 'cdi' ),
				'desc_tip' => __( 'Here, the text you want the customer to see just before the tracking code', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_text_preceding_trackingcode',
			),
			'Url tracking code' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => 'http://www.colissimo.fr/portail_colissimo/suivre.do?colispart=',
				'desc' => __( 'Url for tracking code', 'cdi' ),
				'desc_tip' => __( "Here, the standard url of your carrier. Don't change it if you don't known its url.", 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_url_trackingcode',
			),
			'Parcels return' => array(
				'name' => __( 'Return services :', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Global enabling of Colissimo parcel return function.', 'cdi' ),
				'desc_tip' => __( 'Logged customers will have the capacity, from their order view, to create and print a Colissimo return label. This feature requires a Bussiness contract with Colissimo to access the Web Service Affranchissement.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_parcelreturn',
			),
			'Text preceding parcel return request' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'In case you need to return your parcel, request for a  printable Colissimo return label : ', 'cdi' ),
				'desc' => __( 'Text preceding customer parcel return label request in their order view', 'cdi' ),
				'desc_tip' => __( 'Here, the text your customer will see in its order view to invite him to post a request to get a parcel return label. These information will be seen by the customer only if it is inside the authorized period defined below.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_text_preceding_parcelreturn',
			),
			'Text preceding parcel return print' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( ' Your Colissimo return label is available. Print it and paste it on your parcel. After printing, you can choose the type of postal deposit you want at :', 'cdi' ),
				'desc' => __( 'Text accompanying the parcel return label print button', 'cdi' ),
				'desc_tip' => __( 'Here, the text your customer will see to invite him to print its parcel return label.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_text_preceding_printreturn',
			),
			'Url following text parcel return print' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => 'https://www.colissimo.fr/retourbal/index.htm',
				'cdi',
				'desc' => __( 'Url following the text parcel return print (when necessary)', 'cdi' ),
				'desc_tip' => __( 'Here, when necessary, the url your need to follow the parcel return print text. When blank, no url will be shown', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_url_following_printreturn',
			),
			'Tracking code headers' => array(
				'type' => 'text',
				'css'  => 'width:70%; color:red;',
				'default' => cdi_o_settings_colissimo_trackingheaders_parcelreturn,
				'desc' => __( 'Comma separated list of 2 digits headers of Colissimo tracking codes allowed for a customer parcel return label request.', 'cdi' ),
				'desc_tip' => __( 'Here, the list of Colissimo tracking codes headers (2 digits) which are allowed for a customer parcel return label request. The  standard list can be updated for future new Colissimo products.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_trackingheaders_parcelreturn',
			),
			'Return_product_code' => array(
				'type' => 'text',
				'css'  => 'width:70%; color:red;',
				'default' => cdi_o_settings_colissimo_returnproduct_code,
				'desc' => __( 'Semicolon separated list of relations "Return-product-code=ISO-countrycode list" to be use for parcel returns.', 'cdi' ),
				'desc_tip' => __( 'Here, define the Colissimo return product codes and associated countries.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_returnproduct_code',
			),
			'Return Parcel Service' => array(
				'type' => 'text',
				'default' => 'Service des retours',
				'desc' => __( 'Name of the Company service to whitch the parcels must be returned', 'cdi' ),
				'desc_tip' => __( 'Here, the name of the service in your company that must receice the returned parcel. The rest of the return address is the sender address you set in Web Service Affranchissement settings.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_returnparcelservice',
			),
			'Default Services' => array(
				'name' => __( 'Default services when the CDI Metabox is not filled :', 'cdi' ),
				'type' => 'text',
				'css'  => 'display: none',
				'id'   => 'cdi_o_settings_colissimo_defaultservices',
			),
			'France Country Codes' => array(
				'name' => __( 'France Zone', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:50%; color:red;',
				'default' => cdi_o_settings_colissimo_FranceCountryCodes,
				'desc' => __( 'ISO country codes for Colissimo France zone', 'cdi' ),
				'desc_tip' => __( 'This defines 1- the list of ISO country codes for Colissimo France zone, and 2- the Colissimo product codes with France as destination, for the services : without signature, with signature, pickup location. Theses codes are used only if no product code is set in the order meta box.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_FranceCountryCodes',
			),
			'France Product Codes' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%; color:red;',
				'default' => cdi_o_settings_colissimo_FranceProductCodes,
				'desc' => __( 'Product Codes for France zone', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_FranceProductCodes',
			),
			'Outre-mer Country Codes' => array(
				'name' => __( 'Outre-mer Zone', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:50%; color:red;',
				'default' => cdi_o_settings_colissimo_OutreMerCountryCodes,
				'desc' => __( 'ISO country codes for Colissimo Outre-mer zone', 'cdi' ),
				'desc_tip' => __( 'This defines 1- the list of ISO country codes for Colissimo Outre-mer zone, and 2- the Colissimo product codes with Outre Mer as destination, for the services : without signature, with signature, pickup location. Theses codes are used only if no product code is set in the order meta box.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_OutreMerCountryCodes',
			),
			'Outre Mer Product Codes' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%; color:red;',
				'default' => cdi_o_settings_colissimo_OutreMerProductCodes,
				'desc' => __( 'Product Codes for Outre-mer zone', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_OutreMerProductCodes',
			),
			'Europe Country Codes' => array(
				'name' => __( 'Europe Zone', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:50%; color:red;',
				'default' => cdi_o_settings_colissimo_EuropeCountryCodes,
				'desc' => __( 'ISO country codes for Colissimo Europe zone', 'cdi' ),
				'desc_tip' => __( 'This defines 1- the list of ISO country codes for Colissimo Europe zone, and 2- the Colissimo product codes with Europe as destination, for the services : without signature, with signature, pickup location. Theses codes are used only if no product code is set in the order meta box.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_EuropeCountryCodes',
			),
			'Europe Product Codes' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%; color:red;',
				'default' => cdi_o_settings_colissimo_EuropeProductCodes,
				'desc' => __( 'Product Codes for Europe zone', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_EuropeProductCodes',
			),
			'International Country Codes' => array(
				'name' => __( 'International Zone', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:50%; color:red;',
				'default' => cdi_o_settings_colissimo_InternationalCountryCodes,
				'desc' => __( 'ISO country codes for Colissimo International zone', 'cdi' ),
				'desc_tip' => __( 'This defines 1- the list of ISO country codes for Colissimo International zone, and 2- the Colissimo product codes with International as destination, for the services : without signature, with signature, pickup location. Theses codes are used only if no product code is set in the order meta box.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_InternationalCountryCodes',
			),
			'International Product Codes' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%; color:red;',
				'default' => cdi_o_settings_colissimo_InternationalProductCodes,
				'desc' => __( 'Product Codes for International zone', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_InternationalProductCodes',
			),
			'Exception Product Codes' => array(
				'name' => __( 'Others parameters.', 'cdi' ),
				'type' => 'text',
				'css'  => 'color:red;',
				'default' => cdi_o_settings_colissimo_ExceptionProductCodes,
				'desc' => __( 'Web Service - Comma separated list of "code_to_replace=new_code_to_use" just before the call of Colissimo WS (ex DOM=COLD,DOS=COL)', 'cdi' ),
				'desc_tip' => __( 'This defines Colissimo product codes in exception which have to be replace just before the call of Colissimo Web Service. Generally speaking, the choice of a product code is done in this order : 1) the code for a shipping method as defined in referal, 2) the code given by Colissimo for a pickup location, 3) the code manually forced in meta box order, 4) optionnally if still null in meta box, the code defines in settings for France, Outre mer, International, 5) and finally the exception code rule which has the greatest priority. ', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_ExceptionProductCodes',
			),
			'International home without signature - Country Codes' => array(
				'type' => 'text',
				'css'  => 'color:red;',
				'default' => cdi_o_settings_colissimo_InternationalWithoutSignContryCodes,
				'desc' => __( 'Country codes for which is authorized a without signature Colissimo.', 'cdi' ),
				'desc_tip' => __( 'This defines the list of country codes for which is authorized a without signature Colissimo.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_InternationalWithoutSignContryCodes',
			),
			'International pickup location - Country Codes' => array(
				'type' => 'text',
				'css'  => 'color:red;',
				'default' => cdi_o_settings_colissimo_InternationalPickupLocationContryCodes,
				'desc' => __( 'Pickup location country codes (excluding X00 network)', 'cdi' ),
				'desc_tip' => __( 'This defines the list of destination countries for which Colissimo runs its pickup location service.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_InternationalPickupLocationContryCodes',
			),
			'Country code return' => array(
				'type' => 'text',
				'css'  => 'width:70%; color:red;',
				'default' => cdi_o_settings_colissimo_nochoicereturn_country,
				'desc' => __( 'Comma separated list of 2 digits country codes which cant let a choice for a parcel return in case of no delivery.', 'cdi' ),
				'desc_tip' => __( 'Here, the list of country codes (2 digits) which cant let a choice for a parcel return in case of no delivery. The  standard list can be updated for future new countries.', 'cdi' ),
				'id'   => 'cdi_o_settings_colissimo_nochoicereturn_country',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'cdi_o_settings_section_colissimo_end',
			),
		);
	}

	// Get the settings of Mondial Relay
	public static function get_settings_section_mondialrelay() {
		$route = 'cdi_c_Carrier_' . 'mondialrelay' . '::cdi_carrier_update_settings';
		( $route )();
		update_option(
			'cdi_o_settings_section-mondialrelay-warning',
			__(
				"Warning : 
- To use the Mondial Relay carrier, you must have signed a contract with it giving access to its API services.
- Mondial Relay provides you with a test account ('BDTEST13' account and 'TestAPI1key' password) for the integration of your postage services, choice of Relay Points, parcel tracking, return labels. On the other hand, you should be careful about potential billing when producing postage or return labels with your actual identifiers.
- For all questions relating to the operation of Mondial Relay services, you should contact your Mondial Relay sales representative or Mondial Relay technical support.",
				'cdi'
			)
		);
		return array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_mondialrelay',
			),
			'Mondialrelay section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-mondialrelay',
			),
			'Contract ID' => array(
				'name' => __( 'Mondial Relay contract', 'cdi' ),
				'type' => 'text',
				'default' => 'BDTEST13',
				'desc' => __( 'Web Service - Mondial Relay Contract Number', 'cdi' ),
				'desc_tip' => __( 'Your Mondial Relay contrat number.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_contractnumber',
			),
			'Password' => array(
				'type' => 'text',
				'default' => 'TestAPI1key',
				'desc' => __( 'Web Service - Mondial Relay Password (Test private key has change to TestAPI1key starting from 09/2024)', 'cdi' ),
				'desc_tip' => __( 'Your password at your Mondial Relay contrat (Test private key has change to TestAPI1key starting from 09/2024)', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_password',
			),
			'Mondial Relay section warning' => array(
				'type' => 'textarea',
				'css'  => 'width:70%; height:5em; color:blue;',
				'default' => '',
				'id'   => 'cdi_o_settings_section-mondialrelay-warning',
			),
			'Mondial Relay defaut settings' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Add in referrals settings the Mondial Relay default for shipping méthods: pickup , product code, mandatory phone..', 'cdi' ),
				'desc_tip' => __( 'Will be added at the end of referrals settings - as pickup : cdi_shipping_mondialrelay_pick1, cdi_shipping_mondialrelay_pick2 ; as product code : cdi_shipping_mondialrelay_home1=LD1, cdi_shipping_mondialrelay_home2=LD2, cdi_shipping_mondialrelay_pick1=24R ; as mandatory phone : cdi_shipping_mondialrelay_pick1, cdi_shipping_mondialrelay_pick2 .', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_defautsettings',
			),
			'Output Format - Printing Type' => array(
				'name' => __( 'Label layout :', 'cdi' ),
				'type' => 'select',
				'options' => array(
					'PDF_10x15_300dpi' => 'PDF 10x15',
					'PDF_A5_paysage' => 'PDF A5',
					'PDF_A4_portrait' => 'PDF A4',
				),
				'default' => 'PDF_10x15_300dpi',
				'desc' => __( 'Web Service - Output Format - Printing Type (DPL & ZPL not supported)', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_OutputPrintingType',
			),
			'Text preceding tracking code' => array(
				'name' => __( 'Tracking information for customer:', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'Order shipped. Your Mondial Relay tracking code is : ', 'cdi' ),
				'desc' => __( 'Text preceding tracking code', 'cdi' ),
				'desc_tip' => __( 'Here, the text you want the customer to see just before the tracking code', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_text_preceding_trackingcode',
			),
			'Url tracking code' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => 'http://www.mondialrelay.fr/ww2/public/mr_suivi.aspx?cab=@',
				'desc' => __( 'Url for tracking code', 'cdi' ),
				'desc_tip' => __( "Here, the standard url of your carrier. Don't change it if you don't known its url.", 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_url_trackingcode',
			),
			'Parcels return' => array(
				'name' => __( 'Return services :', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Global enabling of Mondial parcel return function.', 'cdi' ),
				'desc_tip' => __( 'Logged customers will have the capacity, from their order view, to create and print a Mondial Relay return label.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_parcelreturn',
			),
			'Delivery return mode' => array(
				'type' => 'select',
				'options' => array(
					'24R' => __( '24R - Livraison point relais', 'cdi' ),
					'LCC' => __( 'LCC - Livraison Client Chargeur', 'cdi' ),
				),
				'default' => '24R',
				'desc' => __( 'How will be delivered the return parcels to the e-merchand.', 'cdi' ),
				'desc_tip' => __( 'How will be delivered the parcels to e-merchand. 24R is the defaut', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_returndeliver',
			),
			'Merchand return Relay ID' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%;',
				'default' => '',
				'custom_attributes' => array(
					'pattern' => '^(|[A-Z]{2}-[0-9]{4,6})$',
				),
				'desc' => __( 'Merchand Relay ID for its return parcels. Mandatory if 24R. (Structure pp-rrrrrr ; pp=ISO country code, rrrrrr=relay Id)', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_returnrelayid',
			),
			'Text preceding parcel return request' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'In case you need to return your parcel, request for a  printable Mondial Relay return label : ', 'cdi' ),
				'desc' => __( 'Text preceding customer parcel return label request in their order view', 'cdi' ),
				'desc_tip' => __( 'Here, the text your customer will see in its order view to invite him to post a request to get a parcel return label. These information will be seen by the customer only if it is inside the authorized period defined below.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_text_preceding_parcelreturn',
			),
			'Text preceding parcel return print' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'Your Mondial relay return label is available. Print it and paste it on your parcel :', 'cdi' ),
				'desc' => __( 'Text accompanying the parcel return label print button', 'cdi' ),
				'desc_tip' => __( 'Here, the text your customers will see to invite them to print its parcel return label.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_text_preceding_printreturn',
			),
			'Return Parcel Service' => array(
				'type' => 'text',
				'default' => 'Service des retours',
				'desc' => __( 'Name of the Company service to whitch the parcels must be returned', 'cdi' ),
				'desc_tip' => __( 'Here, the name of the service in your company that must receice the returned parcel. The rest of the return address is the sender address you set in Web Service Affranchissement settings.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_returnparcelservice',
			),
			'Default Services' => array(
				'name' => __( 'Default services when the CDI Metabox is not filled :', 'cdi' ),
				'type' => 'text',
				'css'  => 'display: none',
				'id'   => 'cdi_o_settings_mondialrelay_defaultservices',
			),
			'Collecting mode' => array(
				'type' => 'select',
				'options' => array(
					'REL' => __( 'REL - Collecte point relais', 'cdi' ),
					'CDR' => __( 'CDR - Collecte domicile 1P', 'cdi' ),
					'CDS' => __( 'CDS - Collecte domicile 2P', 'cdi' ),
					'CCC' => __( 'CCC - Collecte Client Chargeur', 'cdi' ),
				),
				'default' => 'REL',
				'desc' => __( 'How will be collected the parcels by defaut. Limited choice for some countries.', 'cdi' ),
				'desc_tip' => __( 'How will be collect the parcels. REL is the defaut', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_collect',
			),
			'MR Code pays GP1' => array(
				'name' => __( 'MR Zone 1', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:50%; color:red;',
				'default' => cdi_o_settings_mondialrelay_Gp1Codes,
				'desc' => __( 'ISO country codes for Mondial relay zone 1', 'cdi' ),
				'desc_tip' => __( 'This defines 1- the list of ISO country codes for Mondial relay countries zones, and 2- the Mondialrelay product codes (Deliver code services), for the services : without signature, with signature, pickup location. Theses codes are used only if no product code is set in the order metabox.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_Gp1Codes',
			),
			'MR Code product GP1' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%; color:red;',
				'default' => cdi_o_settings_mondialrelay_Gp1ProductCodes,
				'desc' => __( 'Product Codes for Mondial relay zone 1', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_Gp1ProductCodes',
			),
			'MR Code pays GP2' => array(
				'name' => __( 'MR Zone 2', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:50%; color:red;',
				'default' => cdi_o_settings_mondialrelay_Gp2Codes,
				'desc' => __( 'ISO country codes for Mondial relay zone 2', 'cdi' ),
				'desc_tip' => __( 'This defines 1- the list of ISO country codes for Mondial relay countries zones, and 2- the Mondialrelay product codes (Deliver code services), for the services : without signature, with signature, pickup location. Theses codes are used only if no product code is set in the order metabox.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_Gp2Codes',
			),
			'MR Code product GP2' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%; color:red;',
				'default' => cdi_o_settings_mondialrelay_Gp2ProductCodes,
				'desc' => __( 'Product Codes for Mondial relay zone 2', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_Gp2ProductCodes',
			),
			'MR Code pays GP3' => array(
				'name' => __( 'MR Zone 3', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:50%; color:red;',
				'default' => cdi_o_settings_mondialrelay_Gp3Codes,
				'desc' => __( 'ISO country codes for Mondial relay zone 3', 'cdi' ),
				'desc_tip' => __( 'This defines 1- the list of ISO country codes for Mondial relay countries zones, and 2- the Mondialrelay product codes (Deliver code services), for the services : without signature, with signature, pickup location. Theses codes are used only if no product code is set in the order metabox.', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_Gp3Codes',
			),
			'MR Code product GP3' => array(
				'type' => 'text',
				'css'  => 'width:20%; margin-left:30%; color:red;',
				'default' => cdi_o_settings_mondialrelay_Gp3ProductCodes,
				'desc' => __( 'Product Codes for Mondial relay zone 3', 'cdi' ),
				'id'   => 'cdi_o_settings_mondialrelay_Gp3ProductCodes',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'cdi_o_settings_section_mondialrelay_end',
			),
		);
	}

	// Get the settings of UPS
	public static function get_settings_section_ups() {
		$route = 'cdi_c_Carrier_' . 'ups' . '::cdi_carrier_update_settings';
		( $route )();
		update_option(
			'cdi_o_settings_section-ups-warning',
			__(
				"Warning : 
- To use the UPS carrier, you must have signed a contract with it giving access to its API services.
- UPS provides you with a test mode using specific urls, for the integration of your postage services, choice of access points, package tracking, return labels. To do this, you must comply with the settings offered to you by CDI. On the other hand, you should be careful with your potential billing when producing postage or return labels in 'Production' mode.
- For all questions related to the operation of UPS services, you should contact your UPS Sales Representative or UPS Technical Support.",
				'cdi'
			)
		);
		return array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_ups',
			),
			'UPS section mark' => array(
				'css'  => 'display:none;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-ups',
			),		
			'Compte Number OAuth' => array(
				'name' => __( 'UPS access keys (OAuth Authentification) :', 'cdi' ),
				'type' => 'text',
				'default' => '',
				'desc' => __( 'Web Service - UPS Payment Compte Number OAuth', 'cdi' ),
				'desc_tip' => __( 'Your Payment Compte Number at your UPS contrat', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_comptenumber_oauth',
			),				
			'OAuth ClientId' => array(
				'type' => 'text',
				'default' => '',
				'desc' => __( 'OAuth ClientId of your UPS API Client', 'cdi' ),
				'desc_tip' => __( 'Your OAuth ClientId of your UPS API Client', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_clientid_oauth',
			),			
			'OAuth Secret' => array(
				'type' => 'text',
				'default' => '',
				'desc' => __( 'OAuth ClientSecret of your UPS API Client', 'cdi' ),
				'desc_tip' => __( 'Your OAuth ClientSecret of your UPS API Client', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_clientsecret_oauth',
			),				
			'Ups section warning' => array(
				'type' => 'textarea',
				'css'  => 'width:70%; height:5em; color:blue;',
				'default' => '',
				'id'   => 'cdi_o_settings_section-ups-warning',
			),
			'Ups defaut settings' => array(
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Add in referrals settings the UPS defaut  for shipping méthods: pickup , product code, mandatory phone..', 'cdi' ),
				'desc_tip' => __( 'Will be added at the end of referrals settings - as pickup : cdi_shipping_ups_pick1, cdi_shipping_ups_pick2 ; as product code : cdi_shipping_ups_home1=11,cdi_shipping_ups_home2=07,cdi_shipping_ups_pick1=AP ; as mandatory phone : cdi_shipping_ups_pick1,cdi_shipping_ups_pick2 .', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_defautsettings',
			),
			'Mode Test - Prod' => array(
				'name' => __( 'Production :', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Choose your processing mode : checked is prod mode ; unchecked is test mode', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_modetestprod',
			),
			'Output Format - Printing Type' => array(
				'name' => __( 'Label layout :', 'cdi' ),
				'type' => 'select',
				'options' => array(
					'PDF_10x15_300dpi' => 'PDF 10x15',
					'PDF_A5_paysage' => 'PDF A5',
					'PDF_A4_portrait' => 'PDF A4',
				),
				'default' => 'PDF_10x15_300dpi',
				'desc' => __( 'Web Service - Output Format - Printing Type (DPL & ZPL not supported)', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_OutputPrintingType',
			),
			'Text preceding tracking code' => array(
				'name' => __( 'Tracking information for customer:', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'Order shipped. Your UPS tracking code is : ', 'cdi' ),
				'desc' => __( 'Text preceding tracking code', 'cdi' ),
				'desc_tip' => __( 'Here, the text you want the customer to see just before the tracking code', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_text_preceding_trackingcode',
			),
			'Url tracking code' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => 'https://www.ups.com/track?loc=fr_FR&requester=ST&track=yes&trackNums=',
				'desc' => __( 'Url for tracking code', 'cdi' ),
				'desc_tip' => __( "Here, the standard url of your carrier. Don't change it if you don't known its url.", 'cdi' ),
				'id'   => 'cdi_o_settings_ups_url_trackingcode',
			),
			'Parcels return' => array(
				'name' => __( 'Return services :', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'no',
				'desc' => __( 'Global enabling of UPS return function.', 'cdi' ),
				'desc_tip' => __( 'Logged customers will have the capacity, from their order view, to create and print a UPS return label. This feature requires a contract with UPS to access the Web Service Affranchissement.', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_parcelreturn',
			),
			'Text preceding parcel return request' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'In case you need to return your parcel, request for a  printable UPS return label : ', 'cdi' ),
				'desc' => __( 'Text preceding customer parcel return label request in their order view', 'cdi' ),
				'desc_tip' => __( 'Here, the text your customer will see in its order view to invite him to post a request to get a parcel return label. These information will be seen by the customer only if it is inside the authorized period defined below.', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_text_preceding_parcelreturn',
			),
			'Text preceding parcel return print' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'Your UPS return label is available. Print it and paste it on your parcel :', 'cdi' ),
				'desc' => __( 'Text accompanying the parcel return label print button', 'cdi' ),
				'desc_tip' => __( 'Here, the text your customers will see to invite them to print its parcel return label.', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_text_preceding_printreturn',
			),
			'Return Parcel Service' => array(
				'type' => 'text',
				'default' => 'Service des retours',
				'desc' => __( 'Name of the Company service to whitch the parcels must be returned', 'cdi' ),
				'desc_tip' => __( 'Here, the name of the service in your company that must receice the returned parcel. The rest of the return address is the sender address you set in Web Service Affranchissement settings.', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_returnparcelservice',
			),
			'Default Services' => array(
				'name' => __( 'Default services when the CDI Metabox is not filled :', 'cdi' ),
				'type' => 'text',
				'css'  => 'display: none',
				'id'   => 'cdi_o_settings_ups_defaultservices',
			),
			'Deliver mode' => array(
				'type' => 'select',
				'options' => array(
					'11' => __( '11 - UPS Standard', 'cdi' ),
					'07' => __( '07 - UPS Express', 'cdi' ),
					'AP' => __( 'AP - UPS Access Point', 'cdi' ),
				),
				'default' => '11',
				'desc' => __( 'UPS default "Service Code" for the deliver mode of parcels when nothing is stipulated in CDI Metabox. Also called "Code Product" in CDI Metabox', 'cdi' ),
				'desc_tip' => __( "default for the deliver mode of parcels when nothing is stipulated in CDI Metabox 'Code product'. 11 - UPS Standard is the default ", 'cdi' ),
				'id'   => 'cdi_o_settings_ups_deliver',
			),
			'Ratio max shipping price quote' => array(
				'type' => 'number',
				'default' => '+10',
				'custom_attributes' => array(
					'pattern' => '[0-9]{1,2}',
				),
				'desc' => __( 'Percentage of the cart price (default is 10%) which will be taken to indicate the maximum authorized quotation for the cost of UPS shipping. Will be inserted into the Metabox. The value in Metabox will be the higher value between rate and absolute.', 'cdi' ),
				'desc_tip' => __( 'Here, the percentage of the basket price (by default 10%) which will be taken to indicate the maximum authorized quotation for the cost of UPS shipping. Will be inserted into the Metabox. The value in the Metabox can be changed. The UPS postage request is abandoned if the UPS quote exceeds the value.', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_rateshippingcost',
			),
			'Absolut max shipping price quote' => array(
				'type' => 'number',
				'default' => '+10',
				'custom_attributes' => array(
					'pattern' => '[0-9]{1,2}',
				),
				'desc' => __( 'Max shipping cost in € (default is 10€) which will be taken to indicate the maximum authorized quotation for the cost of UPS shipping. Will be inserted into the Metabox. The value in Metabox will be the higher value between rate and absolute.', 'cdi' ),
				'desc_tip' => __( 'Here, the max shipping cost  of the basket price (by default 10) which will be taken to indicate the maximum authorized quotation for the cost of UPS shipping. Will be inserted into the Metabox. The value in the Metabox can be changed. The UPS postage request is abandoned if the UPS quote exceeds the value.', 'cdi' ),
				'id'   => 'cdi_o_settings_ups_absmaxshippingcost',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'cdi_o_settings_section_ups_end',
			),

		);
	}

	// Get the settings of Collect
	public static function get_settings_section_collect() {
		$route = 'cdi_c_Carrier_' . 'collect' . '::cdi_carrier_update_settings';
		( $route )();
		update_option( 'cdi_o_settings_section-collect-warning', __( 'Warning : ', 'cdi' ) );
		$return  = array(
			'section_title' => array(
				'name'     => ' ',
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cdi_o_settings_section_collect',
			),
		);
		$ref = get_option( 'cdi_o_settings_section-collect-mod-ref' );
		if ( $ref ) {
			$arraymodcollect = array(
				'Collect_mod_id' => array(
					'name' => __( 'COLLECT POINT : ', 'cdi' ) . $ref,
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z][0-9a-zA-Z]{0,9}',
						'required' => 'yes',
					),
					'desc' => __( 'Your Collect point id (mandatory)', 'cdi' ),
					'desc_tip' => __( 'Here, you can change your Collect point id according to your organization.', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-id',
				),
				'Collect_mod_name' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'required' => 'yes',
					),
					'desc' => __( 'Your Collect point name  (mandatory). It is part of the address.', 'cdi' ),
					'desc_tip' => __( 'Here, you can change your Collect point name according to your ornanization.', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-name',
				),
				'Collect_mod_adl1' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'required' => 'yes',
					),
					'desc' => __( 'Address line 1 (mandatory)', 'cdi' ),
					'desc_tip' => __( 'Here, you can change your Collect point address line 1.', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-adl1',
				),
				'Collect_mod_adl2' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
					),
					'desc' => __( 'Address line 2', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-adl2',
				),
				'Collect_mod_adl3' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
					),
					'desc' => __( 'Address line 3', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-adl3',
				),
				'Collect_mod_adcp' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,9}',
						'required' => 'yes',
					),
					'desc' => __( 'Address Zipcode (mandatory)', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-adcp',
				),
				'Collect_mod_adcity' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'required' => 'yes',
					),
					'desc' => __( 'Address city (mandatory)', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-adcity',
				),
				'Collect_mod_adcodcountry' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[A-Z]{2,2}',
						'required' => 'yes',
					),
					'desc' => __( 'Address ISO country code (mandatory)', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-adcodcountry',
				),
				'Collect_mod_indice' => array(
					'name' => __( 'Complements (optional) :', 'cdi' ),
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
					),
					'desc' => __( 'If you want to be more specific for the location. Indicate a nearby monument.', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-indice',
				),
				'Collect_mod_phone' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '^\\+(?:[0-9] ?){6,14}[0-9]$',
					),
					'desc' => __( 'Phone number (international format beginning with a + sign and country code)', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-phone',
				),
				'Collect_mod_parking' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
					),
					'desc' => __( 'Parking : Indicate if parking or where is the nearest parking.', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-parking',
				),
				'Collect_mod_adlat' => array(
					'name' => __( 'GPS position (optional) :', 'cdi' ),
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$',
					),
					'desc' => __( 'Latitude for this point (optional)', 'cdi' ),
					'desc_tip' => __( 'Optional. If not marked, CDI will do a search for approximate coordinates.', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-lat',
				),
				'Collect_mod_adlon' => array(
					'type' => 'text',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$',
					),
					'desc' => __( 'Longitude for this point (optional)', 'cdi' ),
					'desc_tip' => __( 'Optional. If not marked, CDI will do a search for approximate coordinates.', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-lon',
				),
				'Collect_mod_horomon' => array(
					'name' => __( 'Opening time (optional) :', 'cdi' ),
					'type' => 'text',
					'placeholder' => '08:30-12:00 12:45-19:00',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'title' => 'example : 08:30-12:00 12:45-19:00',
					),
					'desc' => __( 'Monday', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-horomon',
				),
				'Collect_mod_horotue' => array(
					'type' => 'text',
					'placeholder' => '08:30-12:00 12:45-19:00',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'title' => 'example : 08:30-12:00 12:45-19:00',
					),
					'desc' => __( 'Tuesday', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-horotue',
				),
				'Collect_mod_horowed' => array(
					'type' => 'text',
					'placeholder' => '08:30-12:00 12:45-19:00',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'title' => 'example : 08:30-12:00 12:45-19:00',
					),
					'desc' => __( 'Wednesday', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-horowed',
				),
				'Collect_mod_horothu' => array(
					'type' => 'text',
					'placeholder' => '08:30-12:00 12:45-19:00',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'title' => 'example : 08:30-12:00 12:45-19:00',
					),
					'desc' => __( 'Thursday', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-horothu',
				),
				'Collect_mod_horofri' => array(
					'type' => 'text',
					'placeholder' => '08:30-12:00 12:45-19:00',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'title' => 'example : 08:30-12:00 12:45-19:00',
					),
					'desc' => __( 'Friday', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-horofri',
				),
				'Collect_mod_horosat' => array(
					'type' => 'text',
					'placeholder' => '08:30-12:00 12:45-19:00',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'title' => 'example : 08:30-12:00 12:45-19:00',
					),
					'desc' => __( 'Saturday', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-horosat',
				),
				'Collect_mod_horosun' => array(
					'type' => 'text',
					'placeholder' => '08:30-12:00',
					'css'  => 'width:70%;',
					'custom_attributes' => array(
						'pattern' => '[0-9a-zA-Z].{1,34}',
						'title' => 'example : 08:30-12:00',
					),
					'desc' => __( 'Sunday', 'cdi' ),
					'id'   => 'cdi_o_settings_section-collect-mod-horosun',
				),
			);
			$return = array_merge( $return, $arraymodcollect );
		}
		$returnnext = array(
			'Collect defaut settings' => array(
				'name' => __( 'Collect settings : ', 'cdi' ),
				'type' => 'checkbox',
				'default' => 'yes',
				'desc' => __( 'Add in referrals settings the Collect defaut for shipping méthods: pickup', 'cdi' ),
				'desc_tip' => __( 'Will be added at the end of referrals settings - as pickup : cdi_shipping_collect_pick1, cdi_shipping_collect_pick2', 'cdi' ),
				'id'   => 'cdi_o_settings_collect_defautsettings',
			),
			'Collect_defaut_startfile' => array(
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => 'CDI-collect-defaut.php',
				'desc' => __( 'Default collection points file (at startup). ', 'cdi' ),
				'desc_tip' => __( "Default collection point file to present if no point is present. (The points are presented after clicking on the 'Save' button). The file must be in the plugin's uploads directory. By default CDI illustrates with a demo file. ", 'cdi' ),
				'id'   => 'cdi_o_settings_collect_defautpointsfile',
			),
			'Text preceding tracking code' => array(
				'name' => __( 'Tracking information for customer:', 'cdi' ),
				'type' => 'text',
				'css'  => 'width:70%;',
				'default' => __( 'Your order is taken into account. Its situation is given by its tracking code: ', 'cdi' ),
				'desc' => __( 'Text preceding tracking code', 'cdi' ),
				'desc_tip' => __( 'Here, the text you want the customer to see just before the tracking code', 'cdi' ),
				'id'   => 'cdi_o_settings_collect_text_preceding_trackingcode',
			),
			'Collect starting status' => array(
				'type' => 'select',
				'options' => array(
					'preparation' => __( 'in preparation for', 'cdi' ),
					'atcollectpoint' => __( 'at collect point', 'cdi' ),
					'courier' => __( 'courier is running', 'cdi' ),													
				),
				'default' => 'atcollectpoint',
				'desc' => __( 'Starting situation for your "Click & Collect" orders: "in preparation for", "at collect point", or "courier is running".', 'cdi' ),
				'desc_tip' => __( '"Click & Collect" orders have 5 successive statuses: "In preparation for", "At collect point", "Courier is running", "Delivered to customer", and "Customer agreement". These statuses can be operated either by the Metabox CDI admin, or by scanning the QR code on the label. ', 'cdi' ),
				'id'   => 'cdi_o_settings_collect_startingstatus',
			),
			'Collect delivered security' => array(
				'type' => 'select',
				'options' => array(
					'applysecurity' => __( 'With security codes to apply', 'cdi' ),
					'free' => __( 'Without security codes', 'cdi' ),
				),
				'default' => 'free',
				'desc' => __( 'Security code to apply when scanning the QRcode for issue (by default no code)', 'cdi' ),
				'desc_tip' => __( 'By default, there is no security codes. It depends on the organizations and whether the scan to mark the delivery will be performed by the customer,  the collect point, or the coursier. The security code is the total price of the order (for the customer)  or a specific code (for the collect point and the coursier).', 'cdi' ),
				'id'   => 'cdi_o_settings_collect_securitycode',
			),
			'Collect section mark' => array(
				'css'  => 'display:none; heigth:0px;',
				'type' => 'number',
				'default' => '1',
				'id'   => 'cdi_o_settings_section-collect',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'cdi_o_settings_section_collect_end',
			),
		);

		return array_merge( $return, $returnnext );
	}

}

