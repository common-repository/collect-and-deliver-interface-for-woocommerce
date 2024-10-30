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
/* CDI Shipping Method (Shipping zone mode)                                             */
/****************************************************************************************/
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields;

// if (class_exists('cdi_c_Shipping_Zone')) return ;
class cdi_c_Shipping {
	public static function init() {
	}
}

define( 'CDIMETHOD', 'colissimo_shippingzone_method' ); // For back compatibility with legacy CDI, method name stay at colissimo_shippingzone_method in WC

$isitenable = get_option( 'cdi_o_settings_methodshipping' );
if ( $isitenable == 'yes' ) {
	$wooversion = cdi_c_Function::cdi_get_woo_version_number();
	if ( $wooversion >= '2.6.0' ) {
		add_action( 'woocommerce_shipping_init', 'cdi_shippingzone_method_init' );
		add_filter( 'woocommerce_shipping_methods', 'cdi_shipping_method_choice_add' );			
		add_filter( 'woocommerce_cart_shipping_method_full_label', 'cdi_woocommerce_cart_shipping_method_full_label', 2, 2 );

		// Here beginning of special code if Woocommerce Blocks Checkout
		// Everything that follows is to overcome WC blocks bugs
		add_action( 'wp_ajax_woocommerce_cart_shipping_method_full_label', 'cdi_callback_woocommerce_cart_shipping_method_full_label' );
		add_action( 'wp_ajax_nopriv_woocommerce_cart_shipping_method_full_label', 'cdi_callback_woocommerce_cart_shipping_method_full_label' );
		function cdi_callback_woocommerce_cart_shipping_method_full_label() {
			if (isset( $_POST['shippingmethod'] ) and isset( $_POST['origlabel'] )) {			
				$dummymethod = (object) array( 'id' => $_POST['shippingmethod'], 'method_id' => CDIMETHOD, ) ;
				$changlabel = cdi_woocommerce_cart_shipping_method_full_label($_POST['origlabel'], $dummymethod) ;
				echo wp_json_encode(array ('shippingmethod' => $_POST['shippingmethod'], 'changlabel' => $changlabel , 'origlabel' => $_POST['origlabel']));					
			}
			wp_die();
		}
	}
}

function cdi_shipping_method_choice_add( $methods ) {
	$methods[ CDIMETHOD ] = 'class_cdi_shipping';
	return $methods;
}

function cdi_shippingzone_method_init() {
	class class_cdi_shipping extends WC_Shipping_Method {
		public $cdi_instance;

		public function __construct( $instance_id = 0 ) {
			$this->id = CDIMETHOD;
			$this->instance_id = absint( $instance_id );
			$this->method_title = __( 'CDI', 'cdi' );
			$this->supports = array( 'shipping-zones', 'instance-settings' );
			$array = array(
				'carrier' => array(
					'title' => __( 'Carrier', 'cdi' ),
					'type' => 'select',
					'options' => array(
						'' => __( 'Choose a carrier', 'cdi' ),
						'colissimo' => __( 'Colissimo', 'cdi' ),
						'ups'      => __( 'UPS', 'cdi' ),
						'mondialrelay' => __( 'Mondial Relay', 'cdi' ),
						'collect'      => __( 'Collect', 'cdi' ),
						'notcdi' => cdi_c_Function::cdi_get_libelle_carrier( 'notcdi' ),
					),
					'default' => '',
					'desc_tip' => __( 'Select the defaut carrier which will collect or deliver yours parcels for this CDI shipping instance', 'cdi' ),
				),
				'title' => array(
					'title' => __( 'Title', 'cdi' ),
					'type' => 'text',
					'desc_tip' => __( 'Mandatory - Title shown in admin shipping options', 'cdi' ),
					'default' => __( 'CDI no carrier', 'cdi' ),
				),
				'prefixshipping' => array(
					'title' => __( 'Prefix ', 'cdi' ),
					'type' => 'text',
					'desc_tip' => __( 'Optional - Prefix of shipping title which will be seen by customer.', 'cdi' ),
					'default' => __( 'CDI no carrier', 'cdi' ),
				),
				'table_rates' => array(
					'type' => 'shipping_table',
					'default' => '',
				),
				'tax_status' => array(
					'type' => 'select',
					'desc_tip' => __( 'Tax Status. To apply or not the tax for the shipping fees when in TVA rates you have checked the shipping tick.', 'cdi' ),
					'class' => 'wc-enhanced-select',
					'default' => 'taxable',
					'options' => array(
						'taxable' => __( 'Taxable', 'woocommerce' ),
						'none' => _x( 'None', 'Tax status', 'woocommerce' ),
					),
				),
			);

			$arrayext = array(
				'shippingdefaulttariffsfile' => array(
					'title' => __( 'Default tariffs file', 'cdi' ),
					'type' => 'text',
					'desc_tip' => __( 'Optional - Default tariffs file to overcome the example tariffs at initialisation.', 'cdi' ),
					'default' => '',
				),
				'shippingclassmode' => array(
					'title' => __( 'Modes:', 'cdi' ),
					'type' => 'checkbox',
					'label' => __( 'Excluding shipping class mode', 'cdi' ),
					'desc_tip' => __( 'Check to have shipping class mode set to Excluding mode', 'cdi' ),
					'default' => 'no',
				),
				'shippingpricemode' => array(
					'type' => 'checkbox',
					'label' => __( 'Price all tax included shipping price mode', 'cdi' ),
					'desc_tip' => __( 'Check to control cart price with all its tax included.', 'cdi' ),
					'default' => 'no',
				),
				'shippingdiscountmode' => array(
					'type' => 'checkbox',
					'label' => __( 'Calculation mode based on the discount price deducted', 'cdi' ),
					'desc_tip' => __( 'Check to control cart price with discount price deducted.', 'cdi' ),
					'default' => 'no',
				),
				'shippingemptypackageweightmode' => array(
					'type' => 'checkbox',
					'label' => __( 'including empty package weight mode', 'cdi' ),
					'desc_tip' => __( 'Check to add empty package weight when considering cart weight.', 'cdi' ),
					'default' => 'no',
				),
				'shippingpackagemode' => array(
					'type' => 'checkbox',
					'label' => __( 'If multi shipping packages (e.g. a Market places plugin activated), consider classes, weights, and prices inside the current shipping package and not for whole cart', 'cdi' ),
					'desc_tip' => __( 'If you process WC multi shipping packages (e.g. a Market places plugin activated), you can choose if classes, weights, and prices must be compute inside the current shipping package or for whole cart. The defaut is to not check this option, i.e. the computation is for the whole cart.', 'cdi' ),
					'default' => 'no',
				),
				'requires' => array(
					'title'   => __( 'Promos :', 'woocommerce' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'default' => '',
					'options' => array(
						''           => __( 'N/A', 'woocommerce' ),
						'coupon'     => __( 'A valid free shipping coupon', 'woocommerce' ),
						'min_amount' => __( 'A minimum order amount', 'woocommerce' ),
						'either'     => __( 'A minimum order amount OR a coupon', 'woocommerce' ),
						'both'       => __( 'A minimum order amount AND a coupon', 'woocommerce' ),
					),
					'description' => __( 'Permet une activation ou non de cette instance CDI Shipping selon le montant du panier et le code promo actif. ', 'woocommerce' ),
				),
				'min_amount' => array(
					'type'        => 'price',
					'placeholder' => wc_format_localized_price( 0 ),
					'description' => __( 'Les clients devront avoir un montant de dépense supérieur à ce montant pour que cette instance CDI soit active (si option choisie ci-dessus).', 'woocommerce' ),
					'default'     => '0',
					'desc_tip'    => true,
				),
				'promomode' => array(
					'type' => 'checkbox',
					'label' => __( '"Excluding promo" mode', 'cdi' ),
					'desc_tip' => __( 'Check to have promo mode set to "Excluding" mode', 'cdi' ),
					'default' => 'no',
				),
				'macroshippingclasses' => array(
					'title'   => __( 'Macros shipping classes :', 'cdi' ),
					'type'    => 'textarea',
					'placeholder' => 'Exemples de macros-classes :
#colis-leger# = ({Catalogue A} or {Catalogue B}) and not ({Lourd} or {Encombrant} or {Catalogue C}) ;
#colis-special# = {Lourd} or {Encombrant} or {Catalogue C} ;
#retrait-depot# = ({Lourd} and {Encombrant}) or {Fragile} ;',
					'css'   => 'min-height:7em;',
					'default'     => '',
					'desc_tip' => __(
						'Shipping class macros are Boolean logic expressions (as in php syntax but strictly use "and" "or" "not" logical operators) on existing Woocommerce classes on the products in the cart, complex and parenthesized expressions being allowed. They enrich the possibilities of selection of the tariffs according to the contents of the cart, the macro-classes allowing a selection at lines tariffs level.
The general syntax is a list of macros separated by ";” with  structure: #exmacro# = Boolean expression ; the Woocommerce product classes referenced in the Boolean expression have the syntax {exclasse}.
Example: #light-package# = {Catalog A} or {Catalog B} and not ({Heavy} or {Bulky}} ; #special-transport# = {Heavy} and {Bulky}; ',
						'cdi'
					),
					'description' => __( 'Macros shipping classes for this instance of shipping method.', 'cdi' ),
				),
			);
			$array = array_merge( $array, $arrayext );

			$this->instance_form_fields = $array;
			$this->enabled = $this->get_option( 'enabled' );
			$title = $this->get_option( 'title' );
			if ( ! $title ) {
				$title = 'CDI'; // Avoid blank
			}
			$this->title = $title;
			$this->carrier = $this->get_option( 'carrier' );
			$this->prefixshipping = $this->get_option( 'prefixshipping' );
			$this->tax_status = $this->get_option( 'tax_status' );
			$this->shippingdefaulttariffsfile = $this->get_option( 'shippingdefaulttariffsfile' );
			$this->shippingclassmode = $this->get_option( 'shippingclassmode' );
			$this->shippingpricemode = $this->get_option( 'shippingpricemode' );
			$this->shippingdiscountmode = $this->get_option( 'shippingdiscountmode' );
			$this->shippingemptypackageweightmode = $this->get_option( 'shippingemptypackageweightmode' );
			$this->shippingpackagemode = $this->get_option( 'shippingpackagemode' );
			$this->min_amount = $this->get_option( 'min_amount', 0 );
			$this->requires   = $this->get_option( 'requires' );
			$this->promomode = $this->get_option( 'promomode' );
			$this->macroshippingclasses = $this->get_option( 'macroshippingclasses' );
			$this->cdi_instance = new class_cdi_shipping_Function();
			$this->table_rates = $this->get_option( 'table_rates' );
			$this->init_instance_settings();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function is_available( $package ) {
			$has_coupon         = false;
			$has_met_min_amount = false;
			if ( in_array( $this->requires, array( 'coupon', 'either', 'both' ) ) ) {
				if ( $coupons = WC()->cart->get_coupons() ) {
					foreach ( $coupons as $code => $coupon ) {
						if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
							$has_coupon = true;
							break;
						}
					}
				}
			}
			if ( in_array( $this->requires, array( 'min_amount', 'either', 'both' ) ) && isset( WC()->cart->cart_contents_total ) ) {
				$total = WC()->cart->get_displayed_subtotal();
				if ( 'incl' === WC()->cart->get_tax_price_display_mode() ) {
					$total = round( $total - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() ), wc_get_price_decimals() );
				} else {
					$total = round( $total - WC()->cart->get_cart_discount_total(), wc_get_price_decimals() );
				}
				if ( $total >= $this->min_amount ) {
					$has_met_min_amount = true;
				}
			}
			switch ( $this->requires ) {
				case 'min_amount':
					$is_available = $has_met_min_amount;
					break;
				case 'coupon':
					$is_available = $has_coupon;
					break;
				case 'both':
					$is_available = $has_met_min_amount && $has_coupon;
					break;
				case 'either':
					$is_available = $has_met_min_amount || $has_coupon;
					break;
				default:
					$is_available = true;
					break;
			}
			if ( $this->promomode == 'yes' ) {
				$is_available = ! $is_available;
			}
			return $is_available;
		}

		function calculate_shipping( $package = array() ) {
			$this->cdi_instance->calculate_shipping( $package, $this );
		}

		function validate_shipping_table_field( $key ) {
			// Only if key is table_rates
			$table_rates = array();
			$currentpackage = $this;
			if ( isset( $_POST[ $currentpackage->id . '_tablerate' ] ) ) {
				$rates = cdi_c_Function::cdi_sanitize_array( $_POST[ $currentpackage->id . '_tablerate' ] );
			} else {
				$rates = null;
			}
			if ( is_array( $rates ) ) {
				foreach ( $rates as $rate ) {
					if ( ! isset( $rate['class'] ) ) {
						$rate['class'] = 'all';
					}
					if ( is_array( $rate['class'] ) ) {
						$xlistclass = implode( ',', $rate['class'] );
					} else {
						$xlistclass = $rate['class'];
					}
					$table_rates[] = array(
						'class' => (string) $xlistclass,
						'methods' => (string) $rate['methods'],
						'pricemin' => (float) $rate['pricemin'],
						'pricemax' => (float) $rate['pricemax'],
						'weightmin' => (float) $rate['weightmin'],
						'weightmax' => (float) $rate['weightmax'],
						'fare' => (float) $rate['fare'],
						'addfees' => (string) $rate['addfees'],
						'method_name' => (string) $rate['method_name'],
					);
				}
			}
			return $table_rates;
		}

		function generate_shipping_table_html() {
			return $this->cdi_instance->generate_shipping_table_html( $this );
		}

		function process_table_rates() {
			$this->cdi_instance->process_table_rates( $this );
		}

		function save_default_costs( $fields ) {
			return $this->cdi_instance->save_default_costs( $fields );
		}

		function load_table_rates() {
			$this->table_rates = $this->get_table_rates();
		}

		function get_table_rates() {
			$return = array_filter( (array) get_option( $this->table_rates ) );
			if ( $return ) {
				return $return;
			} else {
				return $this->get_default_table_rates();
			}
		}

		function get_custom_table_rates( $shippingdefaulttariffsfile ) {
			include( plugin_dir_path( __FILE__ ) . '/../uploads/' . $shippingdefaulttariffsfile );
			if ( ! isset( $startfile ) ) {
				echo '<div class="updated notice"><p>';
				echo __( 'This fares starting file in cdi/uploads is not valid : ', 'cdi' ) . esc_attr( $shippingdefaulttariffsfile );
				echo '</p></div>';
				$return = '';
			} else {
				$return = $startfile;
			}
			return $return;
		}

		function get_default_table_rates() {
			return array();
		}

		function save_default_table_rates() {
			$table_rates = $this->get_default_table_rates();
			update_option( $this->table_rates, $table_rates );
		}

		function get_methods() {
			$shipping_methods = array(
				'home1' => 'home1',
				'home2' => 'home2',
				'home3' => 'home3',
				'home4' => 'home4',
				'home5' => 'home5',
				'home6' => 'home6',
				'pick1' => 'pick1',
				'pick2' => 'pick2',
				'pick3' => 'pick3',
				'pick4' => 'pick4',
				'pick5' => 'pick5',
				'pick6' => 'pick6',
				'shop1' => 'shop1',
				'shop2' => 'shop2',
				'shop3' => 'shop3',
				'shop4' => 'shop4',
				'shop5' => 'shop5',
				'shop6' => 'shop6',
			);
			$extends = get_option( 'cdi_o_settings_methodshipping_extendtermid' );
			$extends = str_replace( ' ', '', $extends );
			if ( $extends && $extends !== '' ) {
				$extends = explode( ',', $extends );
				foreach ( $extends as $extend ) {
					if ( $extend ) {
						 $shipping_methods[ $extend ] = $extend;
					}
				}
			}
			return $shipping_methods;
		}
	}

	class class_cdi_shipping_Function {

		public function calculate_shipping( $package, $currentpackage ) {
			global $woocommerce;
			$currentpackage->rate = array();
			$shipping_rates = $currentpackage->get_option( 'table_rates' );
			cdi_c_Function::cdi_stat( 'SHI-far' );
			if ( empty( $shipping_rates ) ) {
				$shipping_rates = $currentpackage->table_rates;
			}
			// Calc shipping package price or cart price
			if ( $currentpackage->shippingpackagemode == 'yes' ) {
				$line_subtotal = 0;
				$line_subtotal_tax  = 0;
				$line_total = 0;
				$line_tax = 0;
				foreach ( $package['contents'] as $item_id => $values ) {
					$line_subtotal = $line_subtotal + $values['line_subtotal'];
					$line_subtotal_tax  = $line_subtotal_tax + $values['line_subtotal_tax'];
					$line_total = $line_total + $values['line_total'];
					$line_tax = $line_tax + $values['line_tax'];
				}
				if ( $currentpackage->shippingpricemode == 'yes' ) {
					if ( $currentpackage->shippingdiscountmode == 'yes' ) {
						$price = $line_total + $line_tax;
					} else {
						$price = $line_subtotal + $line_subtotal_tax;
					}
				} else {
					if ( $currentpackage->shippingdiscountmode == 'yes' ) {
						$price = $line_total;
					} else {
						$price = $line_subtotal;
					}
				}
				$price = round( $price, wc_get_price_decimals() );
			} else {
				if ( $currentpackage->shippingdiscountmode == 'yes' ) {
					$discount = WC()->cart->get_cart_discount_total();
					$discounttax = WC()->cart->get_cart_discount_tax_total();
				} else {
					$discount = 0;
					$discounttax = 0;
				}
				if ( 'incl' === WC()->cart->get_tax_price_display_mode() ) {
					if ( $currentpackage->shippingpricemode == 'yes' ) {
						$price = round( WC()->cart->get_displayed_subtotal() - ( $discount + $discounttax ), wc_get_price_decimals() );
					} else {
						$price = round( WC()->cart->get_subtotal() - $discount, wc_get_price_decimals() );
					}
				} else {
					$price = round( WC()->cart->get_displayed_subtotal() - $discount, wc_get_price_decimals() );
				}
			}
			// Calc shipping package weight or cart weight
			if ( $currentpackage->shippingpackagemode == 'yes' ) {
				$weight = 0;
				foreach ( $package['contents'] as $item_id => $values ) {			
					if ( $values['data']->needs_shipping() ) {
						$product_id = $values['product_id'];
						$variation_id = $values['variation_id'];
						$quantity = $values['quantity'];
						$product = wc_get_product($product_id) ;
						$weighttoadd = $product->get_weight() ;
						$variation = wc_get_product($variation_id) ;
						if ($variation) {
							$weighttoadd = $variation->get_weight() ;
						}
						if ( ! is_numeric($weighttoadd)) {
							cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ ,  __( ' ===> Error weight for product #', 'cdi' ) . $product_id . ' - ' . $variation, 'exp');
							break ;
						}
						$weight = $weight + ( $weighttoadd * $quantity );						
					}
				}
			} else {
				$weight = (float) $woocommerce->cart->cart_contents_weight;
			}
			if ( get_option( 'woocommerce_weight_unit' ) == 'kg' ) { // Convert kg to g
				$weight = $weight * 1000;
			}
			if ( $currentpackage->shippingemptypackageweightmode == 'yes' ) {
				$weight = $weight + get_option( 'cdi_o_settings_parceltareweight' );
			}
 			$altercartview = array ('weight' => $weight, 'price' => $price) ;			
 			$altercartview = apply_filters( 'cdi_filterstring_shipping_altercartview', $altercartview, $package, $currentpackage );
 			$altercartview = apply_filters( 'cdi_filterarray_shipping_altercartview', $altercartview, $package, $currentpackage );
 			$weight = $altercartview['weight'] ;
 			$price = $altercartview['price'] ;
 
			$classlist = array(); // List of classes in cart or package
			if ( $currentpackage->shippingpackagemode == 'yes' ) {
				$shipping_classes = WC()->shipping->get_shipping_classes();
				if ( ! empty( $shipping_classes ) ) {
					$found_shipping_classes = array();
					foreach ( $package['contents'] as $item_id => $values ) {
						if ( $values['data']->needs_shipping() ) {
							$found_class = $values['data']->get_shipping_class();
							if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
								$found_shipping_classes[ $found_class ] = array();
							}
							$found_shipping_classes[ $found_class ][ $item_id ] = $values;
						}
					}
				}
				foreach ( $found_shipping_classes as $shipping_class => $products ) {
					  $shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
					if ( $shipping_class_term ) {
						if ( ! in_array( $shipping_class_term->slug, $classlist ) ) {
							$classlist[] = $shipping_class_term->slug;
						}
					}
				}
				if ( count( $classlist ) == 0 ) {
					$classlist[] = 'no';
				}
			} else { // process whole cart
				foreach ( $woocommerce->cart->get_cart() as $item ) {
					if ( $item['data']->get_shipping_class() ) {
						$classlist[] = $item['data']->get_shipping_class();
					} else {
						$classlist[] = 'no';
					}
				}
			}

			// Extract and form the Macros shipping classes
			$listmacroclasses = $currentpackage->macroshippingclasses;
			$listmacroclasses = cdi_c_Function::cdi_check_evaluator_syntax( $listmacroclasses );
			if ( $listmacroclasses === false ) {
				$arraysplitmacroclasses = array() ;
			}else{
				$arraysplitmacroclasses = explode( ';', $listmacroclasses, 100 );
			}
			$arraymacroclasses = array();
			// Sanitize has already been performed by WC before recording the macro classes. No need to do more.
			foreach ( $arraysplitmacroclasses as $rule ) {
				if ( $rule ) {
					$arr = explode( '=', $rule );
					if ( isset($arr['0']) and isset($arr['1']) ) {
						$arr['0'] = str_replace( ' ', '', $arr['0'] );
						$arraymacroclasses[ $arr['0'] ] = $arr['1'];
					}
				}
			}
			$arraymacroclasses = apply_filters( 'cdi_filterarray_shipping_macrosclasses_define', $arraymacroclasses, $classlist );

			if ( ! empty( $shipping_rates ) ) {
				$currentshippingrates = array();
				foreach ( $shipping_rates as $rates ) {
					// Buid the array of slug of the shipping classes
					$arrratesclassslug = null;
					$array = explode( ',', $rates['class'] );
					foreach ( $array as $xclassname ) {
						if ( $xclassname == 'all' ) {
							if ( ! isset( $arrratesclassslug['all'] ) ) {
									  $arrratesclassslug[] = 'all';
							}
						} elseif ( $xclassname == 'no' ) {
							if ( ! isset( $arrratesclassslug['no'] ) ) {
								$arrratesclassslug[] = 'no';
							}
						} else {
							$arraywooclasses = array();
							if ( WC()->shipping->get_shipping_classes() ) {
								foreach ( WC()->shipping->get_shipping_classes() as $xshippingclass ) {
									$arraywooclasses[ $xshippingclass->name ] = $xshippingclass->slug;
									if ( stripslashes( $xclassname ) == $xshippingclass->name ) { // Suppress backslash in case of
										if ( ! isset( $arrratesclassslug[ $xshippingclass->slug ] ) ) {
											$arrratesclassslug[] = $xshippingclass->slug;
										}
									}
								}
							}
							// Insert macro-shipping-classes
							if ( substr( $xclassname, 0, 1 ) == '#' and substr( $xclassname, -1 ) == '#' and isset( $arraymacroclasses[ $xclassname ] ) ) {
								$arrratesclassslug[] = $xclassname;
								$code = $arraymacroclasses[ $xclassname ];								
				                               // Replace to PHP Boolean operators.
								$code = str_replace(
									array( ' and ', ' or ', ' not ' ),
									array( ' && ', ' || ', ' ! ' ),
									$code
								);
								while ( true ) {
									$theclass = null;
									$theclass = cdi_c_Function::get_string_between( $code, '{', '}' );
									if ( strlen( $theclass ) >= 1 ) {
										if ( isset( $arraywooclasses[ $theclass ] ) && in_array( $arraywooclasses[ $theclass ], $classlist ) ) {
											$r = ' TRUE ';
										} else {
											$r = ' FALSE ';
										}
										$code = str_replace( '{' . $theclass . '}', $r, $code );
									} else {
										break;
									}
								}
								if ($code) {
									$eval = new cdi_c_Evaluator();
									$bool = $eval->cdi_strictEval( $code );
									$bool = apply_filters( 'cdi_filterarray_shipping_macrosclasses_bool', $bool, $xclassname, $classlist, $arraywooclasses, $code );
									if ( $bool === true || $bool == 1 ) {
										$classlist[] = $xclassname;
									}
								}
							}
						}
					}
					// $arrratesclassslug = classes referenced in the current CDI rate
					// $classlist = classes of products in the cart (or shipping package)
					// Test if rate is to activate
					$is_eligible = true;
					if ( ! ( (float) $price >= (float) $rates['pricemin'] ) ) {
						$is_eligible = false;
					} elseif ( ! ( (float) $price <= (float) $rates['pricemax'] || (float) $rates['pricemax'] == 0 ) ) {
						$is_eligible = false;
					} elseif ( ! ( (float) $weight >= (float) $rates['weightmin'] ) ) {
						$is_eligible = false;
					} elseif ( ! ( (float) $weight <= (float) $rates['weightmax'] || (float) $rates['weightmax'] == 0 ) ) {
						$is_eligible = false;
					} else {
						// Test shipping class condition
						if ( $currentpackage->shippingclassmode == 'yes' ) {
							if ( $arrratesclassslug ) {
								foreach ( $arrratesclassslug as $slug ) {
									if ( ! ( ! isset( $slug ) || ! in_array( $slug, array_map( 'strtolower', $classlist ) ) ) ) {
											$is_eligible = false;
											break;
									} else {
										$is_eligible = true;
									}
								}
							}
						} else {
							if ( ! in_array( 'all', $classlist ) ) {
									$classlist[] = 'all';
							}
							if ( $arrratesclassslug ) {
								foreach ( $arrratesclassslug as $slug ) {
									if ( ! ( ! isset( $slug ) || in_array( $slug, array_map( 'strtolower', $classlist ) ) ) ) {
										  $is_eligible = false;
									} else {
										$is_eligible = true;
										break;
									}
								}
							}
						}
					}
					// Filter for custom decision to activate or not this tariff
					$is_eligible = apply_filters( 'cdi_filterbool_activate_shipping_rate', $is_eligible, $rates, $package );
					if ( $is_eligible ) {
						$currentshippingrates[] = $rates;
					}
				}
				$rgmeth = 0;
				foreach ( $currentshippingrates as $rates ) {
					if ( $rates['method_name'] ) {
						$rgmeth = $rgmeth + 1;
						// Add fees
						$toadd = (float) 0;
						$code = $rates['addfees'];
						if ( isset( $code ) && $code !== '' && strpos( $code, '<?php' ) === 0 ) {
							cdi_c_Function::cdi_debug(
								__LINE__,
								__FILE__,
								__(
									'CDI: direct PHP codes are now deprecated for security reasons.
Now use instead  the filter  "cdi_filterstring_shipping_add_varfare_ . $code ".
$code is a 4 numeric string you have put in addvar field of shipping tariff line.',
									'cdi'
								),
								'exp'
							);
						} elseif ( isset( $code ) && $code !== '' && ( strlen( $code ) == 4 ) && is_numeric( $code ) ) {
							$return = apply_filters( 'cdi_filterstring_shipping_add_varfare_' . $code, 0, $rates, $classlist );
							$toadd = $toadd + $return;
						} else {
							$arrayaddfees = explode( ',', $rates['addfees'] );
							$arrayaddfees = array_map( 'trim', $arrayaddfees );
							foreach ( $arrayaddfees as $addfee ) {
								if ( $addfee ) {
									if ( strpos( $addfee, 'p=+' ) == 0 ) {
										$p = str_replace( 'p=+', '', $addfee );
										$p = (float) ( str_replace( '%', '', $p ) );
										$x = ( ( $price / 100 ) * $p );
										$toadd = $toadd + $x;
									}
									if ( strpos( $addfee, 'w=+' ) == 0 ) {
										$w = str_replace( 'w=+', '', $addfee );
										$x = (float) ( ( $weight / 1000 ) * $w );
										$toadd = $toadd + $x;
									}
								}
							}
						}
						$fare = $rates['fare'] + $toadd;
						$idinstance = $currentpackage->get_instance_id();
						$carrier = $currentpackage->carrier;
						if ( ! $carrier ) { // to process legacy shipping method (for CDI < 4.0.0) if not save of this method has been done
							$carrier = 'colissimo';
						}
						// To bypass inappropriate checking on Checkout Blocs which verifies that CDIMETHOD is part of method id string
						if (\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default() ) {
							$rgmethode = $rgmeth . ':' . CDIMETHOD ;
						}else{
							$rgmethode = $rgmeth ;
						}
						$rate = array(
							'id' => 'cdi_shipping_' . $carrier . '_' . $rates['methods'] . ':' . $idinstance . ':' . $rgmethode,
							'label' => __( $currentpackage->prefixshipping, 'cdi' ) . ' ' . __( $rates['method_name'], 'cdi' ),
							'cost' => $fare,
							'calc_tax' => 'per_order',
							'package' => $package,
						);
						if ( apply_filters( 'cdi_filterbool_tobeornottobe_shipping_rate', true, $rate['id'] ) ) {
							$rate = apply_filters( 'cdi_filterarray_shipping_rate', $rate, $rates, $classlist );
							if ( $rate ) { // Only if $rate exists

								$currentpackage->add_rate( $rate );
							}
						}
						// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $rate, 'msg');
					}
				}
			}
		}

		function save_default_costs( $fields ) {
			$default_pricemin = sanitize_text_field( $_POST['default_pricemin'] );
			$default_pricemax = sanitize_text_field( $_POST['default_pricemax'] );
			$default_fare = sanitize_text_field( $_POST['default_fare'] );
			$fields['pricemin'] = $default_pricemin;
			$fields['pricemax'] = $default_pricemax;
			$fields['fare'] = $default_fare;
			return $fields;
		}

		function generate_thead_tfoot() {
			?>
		<td class="check-column"><input type="checkbox"></td>
	  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Name', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'Name seen by the customer', 'cdi' ); ?>"></span>
			</div></div></th>
	  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Flat rate', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'Flat rate VAT excluded', 'cdi' ); ?>"></span>
			</div></div></th>
		  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Add fees', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'A supplement, excluding VAT, to be added to the package. This may be :
1) a 4-digit numeric code nnnn to call a wordpress filter cdi_filterstring_shipping_add_varfare_nnnn which will calculate and return the value of the additional variable;
2) a comma-separated list of expressions: percentage of cart price and/or additional cost per kg of weight. Ex. syntax: p=+2.5%, w=+5 .
The php short code trigger under is now deprecated for security reasons.', 'cdi' ); ?>"></span>
			</div></div></th>
		  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Shipping class', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'In standard mode,  to activate this rate, an item at least in your cart must have one of the shipping classes. If your have checked the Exclude tick, this rate will activate if none of your items in your cart have none of the shipping classes. Multi select classes allowed.', 'cdi' ); ?>"></span>
			</div></div></th>
		  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Method', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'End of CDI method id', 'cdi' ); ?>"></span>
			</div></div></th>
	  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Min price', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'minimum price, VAT excluded. All taxes included if the mode is checked.', 'cdi' ); ?>"></span>
			</div></div></th>
	  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Max price', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'maximum price, VAT excluded. All taxes included if the mode is checked.', 'cdi' ); ?>"></span>
			</div></div></th>
	  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Min weight', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'minimum weight in g', 'cdi' ); ?>"></span>
			</div></div></th>
	  <th><div style="text-align:center;"><div style="display:inline-block;"><?php _e( 'Max weight', 'cdi' ); ?>
			<span class="woocommerce-help-tip" data-tip=" " title="<?php _e( 'maximum weight in g', 'cdi' ); ?>"></span>
			</div></div></th>
			<?php
		}

		function generate_shipping_table_html( $currentpackage ) {
			global $woocommerce;
			ob_start();
			?>
			<?php include dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Shipping-instance.php'; ?>
			<?php $cdimethod = CDIMETHOD; ?>
	  <tr valign="top">
		<th scope="row" class="titledesc">
			<?php
			  _e( 'Rates', 'cdi' );
			?>
			  :</th>
		  <td class="forminp" id="<?php echo esc_attr( $currentpackage->id ); ?>_table_rates">
		  <table class="shippingrows widefat" cellspacing="0">
		<thead>
		  <tr style="background-color:#E1E1E1">
					  <?php self::generate_thead_tfoot(); ?>
		  </tr>
		</thead>
		<tfoot>
		  <tr style="background-color:#E1E1E1">
					  <?php self::generate_thead_tfoot(); ?>
		  </tr>
		  <tr>
			<th colspan="8">
					  <a href="#" class="add button" style="margin-left: 24px"> <?php _e( 'Add rate', 'cdi' ); ?></a> 
					  <a href="#" class="remove button"><?php _e( 'Delete selected', 'cdi' ); ?></a></th>
		  </tr>
		</tfoot>
		<tbody class="table_rates" style="background-color:#E1E1E1">
				<p><?php _e( 'When the table is empty, click "Save changes" and, for this carrier, examples of delivery rates are offered to inspire you (or your starting rates if specified). ', 'cdi' ); ?></p>
			<?php
			$i = - 1;
			$tablerows = $currentpackage->get_option( 'table_rates' );
			if ( ! $tablerows ) {
				$x = new class_cdi_shipping();
				$shippingdefaulttariffsfile = $currentpackage->get_option( 'shippingdefaulttariffsfile' );
				if ( $shippingdefaulttariffsfile !== '' ) {
					$tablerows = $x->get_custom_table_rates( $shippingdefaulttariffsfile );
				} else {
					$tablerows = $x->get_default_table_rates();
				}
			}
			$listmacroclasses = $currentpackage->macroshippingclasses;
			// Check macroclasses syntax
			if ( $listmacroclasses ) {
				$listmacroclasses = cdi_c_Function::cdi_check_evaluator_syntax( $listmacroclasses );
				if ( $listmacroclasses === false ) {
					$listmacroclasses = null;
					echo '<div class="updated notice"><p>' . __(
						'CDI : The macroclass definition is invalid. 
For the correct syntax, you should refer to the documentation in help or placeholder. Boolean operators allowed are "and", "or", "no" and each definition must be closed with a semicolon ";".',
						'cdi'
					) . '</p></div>';
				}
			}
			if ( $tablerows ) {
				foreach ( $tablerows as $class => $rate ) {
					$zlistclass = explode( ',', str_replace( '\\', '', $rate['class'] ) ); // Suppress ALL backslashes if any
					$methodsData = array();
					$options = '';
					$i++;
					$methods = new class_cdi_shipping();
					$methodsData = $methods->get_methods();
					foreach ( $methodsData as $key => $m ) {
						$selected = '';
						if ( esc_attr( $rate['methods'] ) == $key ) {
							$selected = 'selected="selected"';
						}
						$options .= '<option ' . $selected . ' value="' . $key . '">' . $m . '</option>';
					}
					$shipClass = '';
					$shipclassArr = array();
					if ( $currentpackage->shippingclassmode == 'yes' ) {
						$shipclassArr['no'] = 'No class';
					} else {
						$shipclassArr['all'] = 'All';
						$shipclassArr['no'] = 'No class';
					}

					// Present Macros shipping classes in method settings
					$arraysplitmacroclasses = explode( ';', $listmacroclasses, 100 );
					foreach ( $arraysplitmacroclasses as $rule ) {
						if ( $rule ) {
							$arr = explode( '=', $rule );
							$arr['0'] = str_replace( ' ', '', $arr['0'] );
							$shipclassArr[ $arr['0'] ] = $arr['0'];
						}
					}

					if ( WC()->shipping->get_shipping_classes() ) {
						foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {
							$shipclassArr[ $shipping_class->name ] = $shipping_class->name;
						}
					}
					// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $shipclassArr, 'msg');
					foreach ( $shipclassArr as $key => $m ) {
						// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $m, 'msg');
						$selected = '';
						if ( isset( $zlistclass ) && in_array( $key, $zlistclass ) ) {
							$selected = 'selected="selected"';
						}
						$key = trim( esc_attr( $key ) );
						$m = trim( esc_attr( $m ) );
						if ( $key && $m ) {
							$shipClass .= '<option ' . $selected . ' value="' . $key . '">' . $m . '</option>';
						}
					}
					echo '<tr class="table_rate">
	          <th class="check-column"><input type="checkbox" name="select" /></th>
                  <td><input type="text" value="' . esc_attr( $rate['method_name'] ) . '" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][method_name]' ) . '" style="width: 90%; min-width:100px" class="' . esc_attr( $currentpackage->id . 'field[' . $i . ']' ) . '" placeholder="" size="4" /></td>
		  <td><input type="number" step="any" min="0" value="' . esc_attr( $rate['fare'] ) . '" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][fare]' ) . '" style="width: 90%; min-width:75px" class="' . esc_attr( $currentpackage->id . 'field[' . $i . ']' ) . '" placeholder="0.00" size="4" /></td>
                  <td><input type="text" value="' . esc_attr( $rate['addfees'] ) . '" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][addfees]' ) . '" style="width: 90%; min-width:75px" class="' . esc_attr( $currentpackage->id . 'field[' . $i . ']' ) . '" placeholder="" size="4" /></td>
                  <td><select multiple size="2" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][class][]' ) . '">' . $shipClass . '</select></td>
                  <td><select name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][methods]' ) . '">' . $options . '</select></td>
		  <td><input type="number" step="any" min="0" value="' . esc_attr( $rate['pricemin'] ) . '" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][pricemin]' ) . '" style="width: 90%; min-width:75px" class="' . esc_attr( $currentpackage->id . 'field[' . $i . ']' ) . '" placeholder="0.00" size="4" /></td>
		  <td><input type="number" step="any" min="0" value="' . esc_attr( $rate['pricemax'] ) . '" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][pricemax]' ) . '" style="width: 90%; min-width:75px" class="' . esc_attr( $currentpackage->id . 'field[' . $i . ']' ) . '" placeholder="0.00" size="4" /></td>
                  <td><input type="number" step="any" min="0" value="' . esc_attr( $rate['weightmin'] ) . '" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][weightmin]' ) . '" style="width: 90%; min-width:75px" class="' . esc_attr( $currentpackage->id . 'field[' . $i . ']' ) . '" placeholder="0.00" size="4" /></td>
		  <td><input type="number" step="any" min="0" value="' . esc_attr( $rate['weightmax'] ) . '" name="' . esc_attr( $currentpackage->id . '_tablerate[' . $i . '][weightmax]' ) . '" style="width: 90%; min-width:75px" class="' . esc_attr( $currentpackage->id . 'field[' . $i . ']' ) . '" placeholder="0.00" size="4" /></td>
		</tr>';
				}
			}
			$methods = new class_cdi_shipping();
			$methodsData = $methods->get_methods();
			$options = '';
			foreach ( $methodsData as $key => $m ) {
				$options .= '<option value="' . $key . '">' . $m . '</option>';
			}
			$shipClass = '';
			$shipclassArr = array();
			$shipclassArr['all'] = 'All';
			if ( WC()->shipping->get_shipping_classes() ) {
				foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {
					$shipclassArr[ $shipping_class->name ] = $shipping_class->name;
				}
			}
			$shipClass = '';
			foreach ( $shipclassArr as $key => $m ) {
				$key = esc_attr( $key );
				$m = esc_attr( $m );
				$shipClass .= '<option value="' . $key . '">' . $m . '</option>';
			}
			?>
						</tbody>
					</table>
			<?php
			$varjs = "var cdicurrentpackageid = '" . $currentpackage->id . "' ; " .
				 "var cdishipclass = '" . $shipClass . "' ; " .
				 "var cdishipnotcdiname = '" . cdi_c_Function::cdi_get_libelle_carrier( "notcdi" ) . "' ; " .				 
				 "var cdioptions = '" . $options . "' ; ";
			update_option( 'cdi_o_settings_var_js_admin_shipping', $varjs );
			?>
						
				</td>
			</tr>

			<?php
			return ob_get_clean();
		}
	}
}

function cdi_woocommerce_cart_shipping_method_full_label( $label, $method ) {
	$isicon = get_option( 'cdi_o_settings_methodshippingicon' );
	if ( $isicon == 'yes' ) {
		if ( $method->method_id == CDIMETHOD ) {
			$termid = explode( '_', explode( ':', $method->id )[0] )[3];
			$carrier = explode( '_', explode( ':', $method->id )[0] )[2];
			$iconid = $termid;
			$iconmanaged = array( 'home1', 'home2', 'home3', 'home4', 'home5', 'home6', 'pick1', 'pick2', 'pick3', 'pick4', 'pick5', 'pick6', 'shop1', 'shop2', 'shop3', 'shop4', 'shop5', 'shop6' );
			$carriersmanaged = array( 'colissimo', 'mondialrelay', 'ups', 'collect', 'deliver', 'notcdi' );
			if ( ! in_array( $iconid, $iconmanaged ) ) {
				$iconid = 'home1';
			}
			if ( $termid and in_array( $carrier, $carriersmanaged ) ) {
				$urlshippingicon = plugins_url( 'images/' . 'icon' . substr( $iconid, 0, 4 ) . $carrier . '.png', dirname( __FILE__ ) );
				$urlshippingicon = apply_filters( 'cdi_filterurl_shipping_icon', $urlshippingicon, $termid );
				$label = '<span><img style="display:inline; vertical-align:-5px;" src="' . $urlshippingicon . '"> ' . $label . '</span>';
			}
		}
	}

	return $label;
}

?>
