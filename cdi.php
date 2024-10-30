<?php
/*
 * Plugin Name: Collect and Deliver Interface for Woocommerce
 * Description: CDI - To manage and control your shipments
 * Version: 5.5.6
 * Author: Halyra
 *
 * Text Domain: cdi
 * Domain Path: /languages/
 *
 * Requires at Least: 6.2
 * Tested Up To: 6.6.2
 *
 * Requires Plugins: woocommerce
 * WC requires at least: 6.0.0
 * WC tested up to: 9.3.1
 *
 * Requires PHP: 7.3
 *
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Copyright: (c) 2020  Halyra
 */

__( 'CDI - Collect and Deliver Interface', 'cdi' );

/**
 * This file is part of the CDI (Collect and Deliver Interface) plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Display admin notices
 */
function cdi_general_admin_notices() {
	$noticesadmintodisplay = get_option( 'cdi_o_noticesadmintodisplay' );
	if ( $noticesadmintodisplay ) {
		foreach ( $noticesadmintodisplay as $noticeadmintodisplay ) {
			echo wp_kses_post( $noticeadmintodisplay );
		}
	}
	$noticesadmintodisplay = array();
	update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
}
add_action( 'admin_notices', 'cdi_general_admin_notices' );

/**
 * Set locale and load plugin textdomain
 */
function cdi_load_plugin_textdomain_cdi() {
	$locale = apply_filters( 'cdi_plugin_locale', get_locale(), 'cdi' );
	load_textdomain( 'cdi', WP_LANG_DIR . '/cdi/cdi-' . $locale . '.mo' );
	load_plugin_textdomain( 'cdi', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'cdi_load_plugin_textdomain_cdi' );


/**
 * Check dependency
 */

/*
// multisite  EvENTUELLEMENT A CHANGER A LA PLACE DE L'EXISTANT ?
if ( is_multisite() ) {
  // this plugin is network activated - Woo must be network activated
  if ( is_plugin_active_for_network( plugin_basename(__FILE__) ) ) {
	$need = is_plugin_active_for_network('woocommerce/woocommerce.php') ? false : true;
  // this plugin is locally activated - Woo can be network or locally activated
  } else {
	$need = is_plugin_active( 'woocommerce/woocommerce.php')  ? false : true;
  }
// this plugin runs on a single site
} else {
  $need =  is_plugin_active( 'woocommerce/woocommerce.php') ? false : true;
}
*/

/**
 * Declare HPOS compatibility
 */
add_action('before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
});
            
function cdix_in_array_any( $needles, $haystack ) {
	$return = array_intersect( $needles, $haystack );
	return $return;
}
function cdix_in_array_all( $needles, $haystack ) {
	$return = array_diff( $needles, $haystack );
	return $return;
}

$active_plugins = (array) get_option( 'active_plugins', array() );
if ( is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
}
$must_include = array( 'woocommerce/woocommerce.php' );
$must_exclude = array( 'colissimo-delivery-integration/colissimo-delivery-integration.php' );
$obligplugins = cdix_in_array_all( $must_include, $active_plugins );
$excluplugins = cdix_in_array_any( $must_exclude, $active_plugins );
$returnprocess = true;
if ( count( $obligplugins ) !== 0 ) {
	$messobligplugin = '<div class="notice notice-error is-dismissible"> <p><strong>' . __( 'Error : CDI  (Collect and Deliver Interface)', 'cdi' ) . '</strong>' . __( ' requires that plugin(s)', 'cdi' ) . ' <strong> ' . implode( ' ; ', wp_kses_allowed_html( $obligplugins ) ) . ' </strong> ' . __( 'to be active!. For your security CDI has stopped himself to run. Please review the plugins you need active. ', 'cdi' ) . ' </p></div>';
	$noticesadmintodisplay = get_option( 'cdi_o_noticesadmintodisplay' );
	$noticesadmintodisplay['dependrequired'] = $messobligplugin;
	update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
	$returnprocess = false;
	deactivate_plugins( plugin_basename( __FILE__ ) );
}
if ( count( $excluplugins ) !== 0 ) {
	$messexcluplugin = '<div class="notice notice-info is-dismissible"> <p><strong>' . __( 'Information : CDI  (Collect and Deliver Interface)', 'cdi' ) . '</strong> ' . __( 'notices possible conflict with other active plugin : ', 'cdi' ) . ' ' . implode( ' ; ', wp_kses_allowed_html( $excluplugins ) ) . ' . ' . __( 'CDI  (Collect and Deliver Interface) must be activated alone !.', 'cdi' ) . '</p></div>';
	$noticesadmintodisplay = get_option( 'cdi_o_noticesadmintodisplay' );
	$noticesadmintodisplay['dependconflicting'] = $messexcluplugin;
	update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
	foreach ( $excluplugins as $excluplugin ) {
		deactivate_plugins( plugin_basename( $excluplugin ) );
	}
	$returnprocess = false;
}
if ( ! class_exists( 'SoapClient' ) ) {
	$messoapmissing = '<div class="notice notice-info is-dismissible"> <p><strong>' . __( 'Information : CDI  (Collect and Deliver Interface)', 'cdi' ) . '</strong> ' . __( 'Your installation has not Soap extension installed. We can be afraid that CDI will not fully work.', 'cdi' ) . '</p></div>';
	$noticesadmintodisplay = get_option( 'cdi_o_noticesadmintodisplay' );
	$noticesadmintodisplay['dependsoapmissing'] = $messoapmissing;
	update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
	$returnprocess = false;
}


if ( $returnprocess ) {


	/**
	 * Extend styles and html tags authorized for wp_kses and filter esc_js
	 */
	function cdi_styles( $styles ) {
		$styles[] = 'display';
		$styles[] = 'float';
		return $styles;
	}
	add_filter( 'safe_style_css', 'cdi_styles' );
	function cdi_wp_kses_allowed_html( $tags ) {
		$newtags = $tags;
		$newtags['select'] = array(
			'name' => 1,
			'id' => 1,
			'style' => 1,
		);
		$newtags['option'] = array(
			'value' => 1,
			'style' => 1,
			'styles' => 1,
		);
		$newtags['input'] = array(
			'type' => 1,
			'id' => 1,
			'name' => 1,
			'value' => 1,
			'src' => 1,
		);
		return $newtags;
	}
	add_filter( 'wp_kses_allowed_html', 'cdi_wp_kses_allowed_html' );
	function cdi_js_escape( $wp_safe_text, $text ) {
		// This filter because some wp safe functions not working for some cdi scripts, so drop it for the moment. Requires further analysis for improvement.
		$safe_text = $text;
		$safe_text = wp_check_invalid_utf8( $safe_text );
		// $safe_text = _wp_specialchars( $safe_text, ENT_COMPAT );
		// $safe_text = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes( $safe_text ) );
		$safe_text = str_replace( "\r", '', $safe_text );
		// $safe_text = str_replace( "\n", '\\n', addslashes( $safe_text ) );
		return $safe_text;
	}
	add_filter( 'js_escape', 'cdi_js_escape', 10, 2 );


	/**
	 * Add the styles
	 */
	function cdi_add_styles_css() {
		wp_enqueue_style( 'cdi-admin', plugin_dir_url( __FILE__ ) . 'css/admincdi.css' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}
	add_action( 'admin_enqueue_scripts', 'cdi_add_styles_css' );


	/**
	 * Plugin Activation
	 */
	function cdi_install( $networkwide ) {
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ( $networkwide ) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					cdi_install_db();
				}
				switch_to_blog( $old_blog );
				return;
			}
		}
		cdi_install_db();
	}
	register_activation_hook( __FILE__, 'cdi_install' );


	/**
	 * Activation New blog
	 */
	function cdi_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;
		$old_blog = $wpdb->blogid;
		switch_to_blog( $blog_id );
		cdi_install_db();
		switch_to_blog( $old_blog );
	}
	add_action( 'wpmu_new_blog', 'cdi_new_blog', 10, 6 );


	/**
	 * Plugin Deactivation
	 */
	function cdi_uninstall() {
		// Nothing done here - See uninstall.php file
		global $wpdb;
		// Remove capability cdi_gateway
		$roles = get_editable_roles();
		foreach ( $GLOBALS['wp_roles']->role_objects as $key => $role ) {
			if ( isset( $roles[ $key ] ) && $role->has_cap( 'cdi_gateway' ) ) {
				$role->remove_cap( 'cdi_gateway' );
			}
		}
	}
	register_deactivation_hook( __FILE__, 'cdi_uninstall' );


	/**
	 * DB install
	 */
	function cdi_install_db() {
		global $wpdb;
		$table     = $wpdb->prefix . 'cdi';
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


	/**
	 * Update version and db
	 */
	function cdi_update_version() {
		global $wpdb;
		$currentversion = '5.5.6';
		$oldversion = get_option( 'cdi_o_version' );
		$x = strnatcasecmp( $currentversion, $oldversion );
		if ( ! $oldversion or $x > 0 ) {
			if ( strnatcasecmp( '3.0.0', $oldversion ) > 0 ) { // Update (again) for 3.0.0
				// Nothing to do
			}
		}
		update_option( 'cdi_o_version', $currentversion );
	}
	add_action( 'admin_init', 'cdi_update_version' );

	/**
	 * Set sub link in  plugins extension admin panel
	 */
	function cdi_plugin_row_meta( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) ) {
			$row_meta = array(
				'support' => '<a href="https://wordpress.org/plugins/cdi/faq/" title="Do you need some help?" onclick="window.open(this.href); return false;" target="_self">' . __( 'Do you need some help?', 'cdi' ) . '</a>',
			);
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	add_filter( 'plugin_row_meta', 'cdi_plugin_row_meta', 10, 2 );


	/**
	 * Define "cdi_gateway" capability for admin roles (which can manage_options) and for role names chosen in settings.
	 */
	function cdi_add_caps_gateway() {
		$arrrolename = get_option( 'cdi_o_settings_rolename_gateway' );
		if ( $arrrolename && $arrrolename !== '' ) {
			$roles = get_editable_roles();
			foreach ( $GLOBALS['wp_roles']->role_objects as $key => $role ) {
				if ( isset( $roles[ $key ] ) ) {
					if ( $role->has_cap( 'manage_options' ) or in_array( $role->name, $arrrolename ) ) {
						  $role->add_cap( 'cdi_gateway' );
					} else {
						$role->remove_cap( 'cdi_gateway' );
					}
				}
			}
		}
	}
	add_action( 'admin_init', 'cdi_add_caps_gateway' );


	/**
	 * Define link to settings.
	 */
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cdi_plugin_action_links' );
	function cdi_plugin_action_links( $links ) {
		$setting_link = 'admin.php?page=wc-settings&tab=cdi_tab_settings';
		$plugin_links = array(
			'<a href="' . $setting_link . '">' . __( 'Settings', 'cdi' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}


	/**
	 * Get the settings back if any.
	 */
	if ( null != get_option( 'wc_settings_tab_colissimo_defaulttypeparcel' ) and get_option( 'cdi_o_transferoldsettings' ) == 'silent' ) {
		include_once( 'includes/CDI-Repatriation.php' );
		cdi_c_Repatriation::init();
		$messplugin = '<div class="notice notice-error "> 
    <h2>Welcome to CDI ! / ' . __( 'Bienvenue dans CDI - Collect and Deliver Interface !', 'cdi' ) . '</h2>
    <p>' . __( 'The settings from your anterior Colissimo Delivery Integration has been sucessfully imported in CDI - Collect and Deliver Interface, which is now the only active plugin.', 'cdi' ) . '</p>
    <p style="border:1px solid black;"><strong>' . __( 'Your CDI gateway and your WC shipping zone tables are shared between the 2 plugins.', 'cdi' ) . '</strong></p>   
    </div>';
		$noticesadmintodisplay = get_option( 'cdi_o_noticesadmintodisplay' );
		$noticesadmintodisplay['getbacksettings'] = $messplugin;
		update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
	}
	if ( null != get_option( 'wc_settings_tab_colissimo_defaulttypeparcel' ) and get_option( 'cdi_o_transferoldsettings' ) == null ) {
		$messobligplugin = '<div class="notice notice-error "> 
  <h2>Welcome to CDI ! / ' . __( 'Bienvenue dans CDI - Collect and Deliver Interface !', 'cdi' ) . '</h2>
  <p>' . __( 'If necessary, you can get back the settings of your anterior CDI (Colissimo Delivery Integration)', 'cdi' ) . '</p>
  <p style="border:1px solid black;"><strong>' . __( 'In both cases, your CDI gateway and your WC shipping zone tables are shared between the 2 plugins. Only the other CDI settings are duplicated and copied. <br> If you do not want to get back your CDI Colissimo Delivery Integration  settings, use the "Continue and dismiss this message" button. ', 'cdi' ) . '</strong></p>   
  
  	<p style="display:inline;">
  	  <form id="cdi_activate_plugin_dismiss"  name="cdi_activate_plugin_dismiss" action="" style="display:inline;" method="POST">
	    <button type="submit" id="cdi_activate_plugin_dismiss" name="cdi_activate_plugin_dismiss" style="background-color:white;"><strong>' . __( 'Continue and dismiss this message', 'cdi' ) . '</strong></button>
	  </form>   	
  	  <form id="cdi_activate_plugin_getbacksettings"  name="cdi_activate_plugin_getbacksettings" action="" style="display:inline;" method="POST">
	    <button type="submit" id="cdi_activate_plugin_getbacksettings" name="cdi_activate_plugin_getbacksettings" style="background-color:cyan;">' . __( 'Get back the anterior CDI settings', 'cdi' ) . '</button>
	  </form>  

	</p>	
  </div>';
		$noticesadmintodisplay = get_option( 'cdi_o_noticesadmintodisplay' );
		$noticesadmintodisplay['getbacksettings'] = $messobligplugin;
		update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
	}
	function cdi_activate_plugin_getback() {
		if ( isset( $_POST['cdi_activate_plugin_getbacksettings'] ) ) {
			include_once( 'includes/CDI-Repatriation.php' );
			cdi_c_Repatriation::init();
			$noticesadmintodisplay = array();
			update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
		}
		if ( isset( $_POST['cdi_activate_plugin_dismiss'] ) ) {
			update_option( 'cdi_o_transferoldsettings', 'done' );
			$noticesadmintodisplay = array();
			update_option( 'cdi_o_noticesadmintodisplay', $noticesadmintodisplay );
		}
	}
	add_action( 'admin_init', 'cdi_activate_plugin_getback' );


	/**
	 * Including Classes
	 */

	include_once( 'includes/CDI-Function.php' );
	cdi_c_Function::init();

	include_once( 'includes/CDI-Bibext/CDI-Bibext.php' );
	cdi_c_Bibext::init();

	include_once( 'includes/CDI-Metabox.php' );
	cdi_c_Metabox::init();

	include_once( 'includes/CDI-Metabox-subscription.php' );
	cdi_c_Metabox_subscription::init();

	include_once( 'includes/CDI-Settings.php' );
	cdi_c_Settings::init();

	include_once( 'includes/CDI-Orderlist.php' );
	cdi_c_Orderlist_Action::init();

	include_once( 'includes/CDI-Orderlist-Bulkactions.php' );
	cdi_c_Orderlist_Bulkactions::init();

	include_once( 'includes/CDI-Gateway.php' );
	cdi_c_Gateway::init();

	include_once( 'includes/CDI-Gateway-Manual.php' );
	cdi_c_Gateway_Manual::init();

	include_once( 'includes/CDI-Gateway-Printlabel.php' );
	cdi_c_Gateway_Printlabel::init();

	include_once( 'includes/CDI-Gateway-Custom.php' );
	cdi_c_Gateway_Custom::init();

	include_once( 'includes/CDI-Frontend.php' );
	cdi_c_Frontend::init();

	include_once( 'includes/CDI-Retour-Colis.php' );
	cdi_c_Retour_Colis::init();

	include_once( 'includes/CDI-Print-Localpdf-Labelandcn23.php' );
	cdi_c_Print_Localpdf_Labelandcn23::init();

	include_once( 'includes/CDI-Shipping.php' );
	cdi_c_Shipping::init();

	include_once( 'includes/CDI-Reference-Livraisons.php' );
	cdi_c_Reference_Livraisons::init();

	include_once( 'includes/CDI-Pdf-Workshop.php' );
	cdi_c_Pdf_Workshop::init();

	include_once( 'includes/CDI-Gateway-Bordereaux.php' );
	cdi_c_Gateway_Bordereaux::init();

	include_once( 'includes/CDI-Gateway-Debug.php' );
	cdi_c_Gateway_Debug::init();

	include_once( 'includes/CDI-Carrier-colissimo/exec.php' );
	cdi_c_Carrier_colissimo::init();

	include_once( 'includes/CDI-Carrier-mondialrelay/exec.php' );
	cdi_c_Carrier_mondialrelay::init();

	include_once( 'includes/CDI-Carrier-ups/exec.php' );
	cdi_c_Carrier_ups::init();

	include_once( 'includes/CDI-Carrier-collect/exec.php' );
	cdi_c_Carrier_collect::init();

	include_once( 'includes/CDI-Carrier-deliver/exec.php' );
	cdi_c_Carrier_deliver::init();

	include_once( 'includes/CDI-Carrier-notcdi/exec.php' );
	cdi_c_Carrier_notcdi::init();

}


