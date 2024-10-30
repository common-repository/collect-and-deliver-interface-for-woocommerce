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
 * Bulk print pdf file when requested from admin
 */

// For QrCode
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class cdi_c_Pdf_Workshop {

	public static function init() {
		add_action( 'admin_init', __CLASS__ . '::cdi_general_send_csv' );
	}

	public static function cdi_globalprint_storeworking( $lbfilename, $filecontent ) {
		//
		// Store current pdf in cdistore/working file to get it back after
		//
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		global $wp_filesystem;
		$return = null;
		while ( true ) {
			$upload_dir = wp_upload_dir();
			$dircdistore = trailingslashit( $upload_dir['basedir'] ) . 'cdistore';
			$url = wp_nonce_url( 'plugins.php?page=cdi' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $url, 'tec' );
				break;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $creds, 'tec' );
				break;
			}
			if ( ! file_exists( $dircdistore ) ) { // create cdistore dir if not exist
				if ( ! $wp_filesystem->mkdir( $dircdistore ) ) {
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $dircdistore, 'tec' );
					break;
				}
			}
			chmod( $dircdistore, 0750 ); // to avoid external reading
			$lbfilename = trailingslashit( $dircdistore ) . $lbfilename;
			$result = $wp_filesystem->delete( $lbfilename ); // if exist suppress before replace
			if ( ! $wp_filesystem->put_contents( $lbfilename, $filecontent, FS_CHMOD_FILE ) ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $lbfilename, 'tec' );
				break;
			}
			$return = $lbfilename;
			break;
		}
		return $return;
		// End of store in cdistore/file
	}

	public static function cdi_globalprint_deleteworking( $realfilename ) {
		//
		// Delete current pdf or Gif in cdistore/working after working
		//
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		global $wp_filesystem;
		$result = null;
		$result = $wp_filesystem->delete( $realfilename ); // if exist suppress
		return $result;
	}

	public static function AddPdfFile( $pdf, $file ) {
		$nbPage = $pdf->setSourceFile( $file );
		for ( $i = 1; $i <= $nbPage; $i++ ) {
			$tplidx = $pdf->ImportPage( $i );
			$size = $pdf->getTemplatesize( $tplidx );
			$pdf->AddPage( 'P', array( $size['w'], $size['h'] ) );
			$pdf->useTemplate( $tplidx );
		}
	}

	public static function AddPdfFile_format_pdfA4( $pdf, $file ) {
		// Add sur format A4 Paysage après rotation de 270 degrès
		$nbPage = $pdf->setSourceFile( $file );
		for ( $i = 1; $i <= $nbPage; $i++ ) {
			$tplidx = $pdf->ImportPage( $i );
			$size = $pdf->getTemplatesize( $tplidx );
			$pdf->AddPage( 'L', array( $size['h'], $size['w'] ), -90 );
			$pdf->useTemplate( $tplidx );
		}
	}

	public static function AddPdfFile_format_pdfA5paysage( $pdf, $file ) {
		// Add sur format A5 Paysage après rotation de 270 degrès
		$nbPage = $pdf->setSourceFile( $file );
		for ( $i = 1; $i <= $nbPage; $i++ ) {
			$tplidx = $pdf->ImportPage( $i );
			$size = $pdf->getTemplatesize( $tplidx );
			$pdf->AddPage( 'P', array( $size['h'], $size['w'] ), -90 );
			$pdf->useTemplate( $tplidx );
		}
	}

	public static function AddPdfFile_format_pdfA4portrait( $pdf, $file ) {
		// Add sur format A4 Portrait
		$nbPage = $pdf->setSourceFile( $file );
		for ( $i = 1; $i <= $nbPage; $i++ ) {
			$tplidx = $pdf->ImportPage( $i );
			$size = $pdf->getTemplatesize( $tplidx );
			$pdf->AddPage( 'P', array( $size['h'], $size['w'] ) );
			$pdf->useTemplate( $tplidx );
		}
	}

	public static function AddPdfFile_format_10x15( $pdf, $file ) {
		// Add sur format 10x15 Portrait
		$nbPage = $pdf->setSourceFile( $file );
		for ( $i = 1; $i <= $nbPage; $i++ ) {
			$tplidx = $pdf->ImportPage( $i );
			$size = $pdf->getTemplatesize( $tplidx );
			$pdf->AddPage( 'P', array( $size['w'], $size['h'] ) );
			$pdf->useTemplate( $tplidx );
		}
	}

	public static function cdi_convert_giftopdf( $gif64base, $display = 'L', $format = 'A4', $rotation = '0', $orderid = 1 ) {
		// Convert a gif to pdf. Input and output are in base64
		$pdf = new FPDI();
		$gif = base64_decode( $gif64base );
		$lbfilename = 'Bulk-label-working-ups' . $orderid . '.gif';
		$realfilename = self::cdi_globalprint_storeworking( $lbfilename, $gif );
		$pdf->AddPage( $display, $format, $rotation );
		$pdf->Image( $realfilename, 5, 5, $format[0] - 10, $format[1] - 10 ); // Margins of 5
		$resultpdf = $pdf->Output( 'S' );
		$resultpdf64base = base64_encode( $resultpdf );
		$result = self::cdi_globalprint_deleteworking( $realfilename );
		return $resultpdf64base;
	}

	public static function cdi_bulk_label_pdf( $selected, $format ) {
		$return = array(
			'error' => 'no label found',
			'nbcolis' => 0,
			'resultpdf' => null,
		);
		$error = '';
		global $woocommerce;
		if ( current_user_can( 'cdi_gateway' ) ) {
			$nbcolis = count( $selected );
			if ( $nbcolis > 0 ) {
				$cdi_nbrorderstodo = 0;
				$parceltoprocess = array();
				foreach ( $selected as $parcel ) {
					$tracking = cdi_c_Function::get_string_between( $parcel, '[', ']' );
					if ( $tracking ) {
						$orderid = str_replace( ' [' . $tracking . ']', '', $parcel );
						$order = wc_get_order( $orderid );
						$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $orderid );
						if ( $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true ) {
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
							$parceltoprocess[] = $orderid;
						}
					}
				}
				if ( $cdi_nbrorderstodo > 0 ) {
					$cdi_nbrorderstoprocess = 0;
					$pdf = new FPDI();
					foreach ( $parceltoprocess as $parcel ) {
						$cdi_loclabel = cdi_c_Function::cdi_uploads_get_contents( $parcel, 'label' );
						if ( $cdi_loclabel ) {
							$cdi_nbrorderstoprocess = $cdi_nbrorderstoprocess + 1;
							cdi_c_Function::cdi_stat( 'BLK-lab' );
							$cdi_loclabel_pdf = base64_decode( $cdi_loclabel );
							$lbfilename = 'Bulk-label-working-' . $cdi_nbrorderstoprocess . '.pdf';
							$realfilename = self::cdi_globalprint_storeworking( $lbfilename, $cdi_loclabel_pdf );
							if ( null !== $realfilename ) {
								if ( $format == 'PDF_A4_300dpi' ) {
									self::AddPdfFile_format_pdfA4( $pdf, $realfilename );
								} elseif ( $format == 'PDF_10x15_300dpi' ) {
									self::AddPdfFile_format_10x15( $pdf, $realfilename );
								} elseif ( $format == 'PDF_A4_portrait' ) {
									self::AddPdfFile_format_pdfA4portrait( $pdf, $realfilename );
								} elseif ( $format == 'PDF_A5_paysage' ) {
									self::AddPdfFile_format_pdfA5paysage( $pdf, $realfilename );
								} else {
										  $result = self::cdi_globalprint_deleteworking( $realfilename );
										  $message = 'Error output format : ' . $format; // Notice admin to rework
										  cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Error output format : ' . $format, 'exp' );
										  $error = $message;
										  break;
								}
							}
							$result = self::cdi_globalprint_deleteworking( $realfilename );
						} else {
							cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Invalid parcel : ' . $parcel, 'exp' );
						}
					}
					if ( ! $error and $cdi_nbrorderstoprocess > 0 ) {
						$resultpdf = $pdf->Output( 'S' );
						$resultpdf = base64_encode( $resultpdf );
					} else {
						$resultpdf = null;
					}
					$return['error'] = $error;
					$return['nbcolis'] = $cdi_nbrorderstoprocess;
					$return['resultpdf'] = $resultpdf;
				}
			}
		} // End current_user_can
		return $return;
	}

	public static function cdi_bulk_cn23_pdf( $selected, $format ) {
		$return = array(
			'error' => 'no cn23 found',
			'nbcolis' => 0,
			'resultpdf' => null,
		);
		$error = '';
		global $woocommerce;
		if ( current_user_can( 'cdi_gateway' ) ) {
			$nbcolis = count( $selected );
			if ( $nbcolis > 0 ) {
				$cdi_nbrorderstodo = 0;
				$parceltoprocess = array();
				foreach ( $selected as $parcel ) {
					$tracking = cdi_c_Function::get_string_between( $parcel, '[', ']' );
					if ( $tracking ) {
						$orderid = str_replace( ' [' . $tracking . ']', '', $parcel );
						$order = wc_get_order( $orderid );
						$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $orderid );
						if ( $order->get_meta( '_cdi_meta_exist_uploads_cn23' ) == true ) {
							$cdi_nbrorderstodo = $cdi_nbrorderstodo + 1;
							$parceltoprocess[] = $orderid;
						}
					}
				}
				if ( $cdi_nbrorderstodo > 0 ) {
					$cdi_nbrorderstoprocess = 0;
					$pdf = new FPDI();
					foreach ( $parceltoprocess as $parcel ) {
						$cdi_loccn23 = cdi_c_Function::cdi_uploads_get_contents( $parcel, 'cn23' );
						if ( $cdi_loccn23 ) {
							$cdi_nbrorderstoprocess = $cdi_nbrorderstoprocess + 1;
							cdi_c_Function::cdi_stat( 'BLK-cn23' );
							$cdi_loccn23_pdf = base64_decode( $cdi_loccn23 );
							$lbfilename = 'Bulk-cn23-working-' . $cdi_nbrorderstoprocess . '.pdf';
							$realfilename = self::cdi_globalprint_storeworking( $lbfilename, $cdi_loccn23_pdf );
							if ( null !== $realfilename ) {
								if ( $format == 'PDF_A4_300dpi' ) {
									self::AddPdfFile_format_pdfA4( $pdf, $realfilename );
								} elseif ( $format == 'PDF_10x15_300dpi' ) {
									self::AddPdfFile_format_10x15( $pdf, $realfilename );
								} elseif ( $format == 'PDF_A4_portrait' ) {
									self::AddPdfFile_format_pdfA4portrait( $pdf, $realfilename );
								} elseif ( $format == 'PDF_A5_paysage' ) {
									self::AddPdfFile_format_pdfA5paysage( $pdf, $realfilename );
								} else {
										  $result = self::cdi_globalprint_deleteworking( $realfilename );
										  $message = 'Error output format : ' . $format; // Notice admin to rework
										  cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Error output format : ' . $format, 'tec' );
										  $error = $message;
										  break;
								}
							}
							$result = self::cdi_globalprint_deleteworking( $realfilename );
						} else {
							cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Invalid parcel : ' . $parcel, 'exp' );
						}
					}
					if ( ! $error and $cdi_nbrorderstoprocess > 0 ) {
						$resultpdf = $pdf->Output( 'S' );
						$resultpdf = base64_encode( $resultpdf );
					} else {
						$resultpdf = null;
					}
					$return['error'] = $error;
					$return['nbcolis'] = $cdi_nbrorderstoprocess;
					$return['resultpdf'] = $resultpdf;
				}
			}
		} // End current_user_can
		return $return;
	}

	public static function cdi_general_send_csv() {
		if ( get_option( 'cdi_o_settings_generalsendcsv' ) ) {
			if ( current_user_can( 'cdi_gateway' ) ) {
				$thecsvfile = get_option( 'cdi_o_settings_generalsendcsv' )['filename'];
				$thecontent = get_option( 'cdi_o_settings_generalsendcsv' )['content'];
				$out = fopen( 'php://output', 'w' );
				header( 'Content-type: text/csv' );
				header( 'Content-Disposition: attachment; filename=' . $thecsvfile );
				fwrite( $out, $thecontent );
				fclose( $out );
				delete_option( 'cdi_o_settings_generalsendcsv' );
				die();
			} // End current_user_can
		} // End $_POST['cdi_general_send_csv'
	} // End function cdi_general_send_csv

	public static function cdi_build_cn23_pdf( $order_id, $tracking, $array_for_carrier, $r = 'no' ) {
		global $woocommerce;
		$order = wc_get_order( $order_id );
		$ordernumber = $order->get_order_number();
		$workfilename = trailingslashit( plugin_dir_path( __DIR__ ) ) . 'images/CN23-Model-OECD-CDI-A4-noguide.pdf';

		$pdf = new FPDI();
		$pdf->AddPage( 'Portrait', 'a4' );
		$pdf->setSourceFile( $workfilename );
		$tplIdx = $pdf->importPage( 1 );
		$pdf->useTemplate( $tplIdx );
		$pdf->SetFont( 'Arial', '', 10 );
		$pdf->SetTextColor( 0, 0, 0 );

		// Reference
		$x = 11;
		$y = 8;  // x=col y=line
		$pdf->SetFont( 'Arial', '', 10 );
		$pdf->SetFont( 'Arial', 'B', 16 );
		if ( $r == 'no' ) {
			$pdf->Text( $x, $y, utf8_decode( strtoupper( WC()->countries->countries[ get_option( 'cdi_o_settings_merchant_CountryCode' ) ] ) ) );
		} else {
			$pdf->Text( $x, $y, utf8_decode( strtoupper( WC()->countries->countries[ $array_for_carrier['shipping_country'] ] ) ) );
		}
		$x = 45;
		$y = 7;  // x=col y=line
		$pdf->SetFont( 'Arial', '', 10 );
		$pdf->Text( $x, $y, utf8_decode( get_option( 'cdi_installation_id' ) . ' ' . $order_id . '(' . $ordernumber . ')' ) );

		// Carrier
		$x = 125;
		$y = 20;  // x=col y=line
		$carrierlabel = str_replace( array( 'colissimo', 'mondialrelay', 'ups', 'collect', 'deliver' ), array( 'COLISSIMO', 'MONDIAL RELAY', 'UPS', 'COLLECT', 'DELIVER' ), $array_for_carrier['carrier'] );
		$pdf->SetFont( 'Arial', 'I', 10 );
		// $pdf->Text($x,$y+0,utf8_decode ('Transporteur : Référence : '));
		$pdf->SetFont( 'Arial', 'B', 14 );
		$pdf->Text( $x, $y + 5, utf8_decode( $carrierlabel . ' : ' . $tracking ) );
		self::cdi_EAN128_pdf( $pdf, 125, 28, $tracking, 70, 15 );

		// From
		if ( $r == 'no' ) {
			$x = 16;
			$y = 17;  // x=col y=line
		} else {
			$x = 16;
			$y = 45;  // x=col y=line
		}
		$pdf->SetFont( 'Arial', '', 10 );
		$shipperphone = get_option( 'cdi_o_settings_merchant_fixphone' );
		if ( get_option( 'cdi_o_settings_merchant_cellularphone' ) != '' ) {
			$shipperphone = get_option( 'cdi_o_settings_merchant_cellularphone' );
		}
		$pdf->Text( $x, $y + 0, utf8_decode( '=> ' . $shipperphone . ' - ' . get_option( 'cdi_o_settings_merchant_Email' ) ) );
		$pdf->Text( $x, $y + 5, utf8_decode( get_option( 'cdi_o_settings_merchant_CompanyName' ) ) );
		$pdf->Text( $x, $y + 11, utf8_decode( get_option( 'cdi_o_settings_merchant_Line1' ) . ' ' . get_option( 'cdi_o_settings_merchant_Line2' ) ) );
		$pdf->Text( $x, $y + 16, utf8_decode( get_option( 'cdi_o_settings_merchant_ZipCode' ) . ' ' . get_option( 'cdi_o_settings_merchant_City' ) ) );
		$pdf->Text( $x, $y + 22, utf8_decode( WC()->countries->countries[ get_option( 'cdi_o_settings_merchant_CountryCode' ) ] ) );

		// To
		if ( $r == 'no' ) {
			$x = 16;
			$y = 45;  // x=col y=line
		} else {
			$x = 16;
			$y = 17;  // x=col y=line
		}
		$pdf->SetFont( 'Arial', '', 10 );
		$shiptophone = $array_for_carrier['billing_phone'];
		$shiptophonecellular = cdi_c_Function::cdi_sanitize_mobilenumber( $array_for_carrier['billing_phone'], $array_for_carrier['shipping_country'] );
		if ( $shiptophonecellular ) {
			$shiptophone = $shiptophonecellular;
		}
		$shiptophone = apply_filters( 'cdi_filterstring_auto_mobilenumber', $shiptophone, $order_id );
		$pdf->Text( $x, $y + 0, utf8_decode( '=> ' . $shiptophone . ' - ' . $array_for_carrier['billing_email'] ) );
		$pdf->Text( $x, $y + 5, utf8_decode( $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'] ) );
		$pdf->Text( $x, $y + 11, utf8_decode( $array_for_carrier['shipping_address_1'] . ' ' . $array_for_carrier['shipping_address_2'] ) );
		$pdf->Text( $x, $y + 16, utf8_decode( $array_for_carrier['shipping_postcode'] . ' ' . $array_for_carrier['shipping_city'] . $array_for_carrier['shipping_state'] ) );
		$pdf->Text( $x, $y + 22, utf8_decode( WC()->countries->countries[ $array_for_carrier['shipping_country'] ] ) );

		// Totals
		$x = 100;
		$y = 104;  // x=col y=line
		$pdf->SetFont( 'Arial', '', 10 );
		$pdf->Text( $x - 40, $y + 0, utf8_decode( 'Global package ===>' ) );
		$pdf->Text( $x, $y + 0, utf8_decode( (float)($array_for_carrier['parcel_weight']) / 1000 . ' Kg' ) );
		$pdf->Text( $x + 23, $y + 0, utf8_decode( $order->get_total() ) . ' ' . chr( 128 ) );
		$pdf->Text( $x + 46, $y + 0, utf8_decode( 'Shipping: ' . $array_for_carrier['cn23_shipping'] ) . ' ' . chr( 128 ) );

		// Category
		$x = 0;
		$y = 0;  // x=col y=line
		$pdf->SetFont( 'Arial', '', 10 );
		if ( $r == 'no' ) {
			$cat = $array_for_carrier['cn23_category'];
		} else {
			$cat = 6;
		}
		$arrx = array( '0', '15', '55', '55', '15', '95', '55' );
		$arry = array( '0', '116', '111', '121', '121', '111', '116' );
		$x = $arrx[ $cat ];
		$y = $arry[ $cat ];
		$pdf->Text( $x, $y, utf8_decode( 'X' ) );

		// Articles
		$x = 5;
		$y = 78;  // x=col y=line
		$pdf->SetFont( 'Arial', '', 10 );
		for ( $nbart = 0; $nbart <= 99; $nbart++ ) {
			if ( ! isset( $array_for_carrier[ 'cn23_article_description_' . $nbart ] ) ) {
				break;
			}
			$artweight = $array_for_carrier[ 'cn23_article_weight_' . $nbart ];
			if ( $artweight and is_numeric( $artweight ) and $artweight !== 0 ) { // Supp Virtual and no-weighted products
				$y = $y + 5;
				if ( $y >= 103 and $y < 140 ) {
					$y = 170;
				}
				$pdf->Text( $x + 11, $y, utf8_decode( $array_for_carrier[ 'cn23_article_description_' . $nbart ] ) );
				$pdf->Text( $x + 75, $y, utf8_decode( $array_for_carrier[ 'cn23_article_quantity_' . $nbart ] ) );
				$pdf->Text( $x + 96, $y, utf8_decode( (float)$array_for_carrier[ 'cn23_article_weight_' . $nbart ] / 1000 ) );
				$pdf->Text( $x + 117, $y, utf8_decode( $array_for_carrier[ 'cn23_article_value_' . $nbart ] ) . ' ' . chr( 128 ) );
				$pdf->Text( $x + 145, $y, utf8_decode( $array_for_carrier[ 'cn23_article_hstariffnumber_' . $nbart ] ) );
				$pdf->Text( $x + 173, $y, utf8_decode( WC()->countries->countries[ $array_for_carrier[ 'cn23_article_origincountry_' . $nbart ] ] ) );
			}
		}

		// Posted
		$x = 146;
		$y = 116;  // x=col y=line
		$pdf->SetFont( 'Arial', '', 10 );
		$pdf->Text( $x + 0, $y + 0, utf8_decode( 'Carrier : ' ) );
		$pdf->Text( $x + 20, $y + 0, utf8_decode( $carrierlabel ) );
		$pdf->Text( $x + 0, $y + 5, utf8_decode( 'Posted : ' ) );
		$pdf->Text( $x + 20, $y + 5, utf8_decode( date( 'jS \of F Y' ) ) );

		// Signature
		$x = 143;
		$y = 152;  // x=col y=line
		$pdf->SetFont( 'Arial', '', 10 );
		$pdf->Text( $x + 0, $y + 0, utf8_decode( date( 'jS \of F Y' ) ) );

		$resultpdf = $pdf->Output( 'S' );
		$resultpdf = base64_encode( $resultpdf );
		return $resultpdf;
	}

	public static function cdi_EAN128_pdf( $pdf, $x, $y, $code, $w, $h ) {
		// Thanks to Roland Gautier
		$ABCset = '';
		$Aset = '';
		$Bset = '';
		$Cset = '';
		$SetFrom = array(
			'A' => '',
			'B' => '',
			'C' => '',
		);
		$SetTo = array(
			'A' => '',
			'B' => '',
			'C' => '',
		);
		$JStart = array(
			'A' => 103,
			'B' => 104,
			'C' => 105,
		);
		$JSwap = array(
			'A' => 101,
			'B' => 100,
			'C' => 99,
		);
		$tab128 = array();
		$tab128[] = array( 2, 1, 2, 2, 2, 2 );           // 0 : [ ]
		$tab128[] = array( 2, 2, 2, 1, 2, 2 );           // 1 : [!]
		$tab128[] = array( 2, 2, 2, 2, 2, 1 );           // 2 : ["]
		$tab128[] = array( 1, 2, 1, 2, 2, 3 );           // 3 : [#]
		$tab128[] = array( 1, 2, 1, 3, 2, 2 );           // 4 : [$]
		$tab128[] = array( 1, 3, 1, 2, 2, 2 );           // 5 : [%]
		$tab128[] = array( 1, 2, 2, 2, 1, 3 );           // 6 : [&]
		$tab128[] = array( 1, 2, 2, 3, 1, 2 );           // 7 : [']
		$tab128[] = array( 1, 3, 2, 2, 1, 2 );           // 8 : [(]
		$tab128[] = array( 2, 2, 1, 2, 1, 3 );           // 9 : [)]
		$tab128[] = array( 2, 2, 1, 3, 1, 2 );           // 10 : [*]
		$tab128[] = array( 2, 3, 1, 2, 1, 2 );           // 11 : [+]
		$tab128[] = array( 1, 1, 2, 2, 3, 2 );           // 12 : [,]
		$tab128[] = array( 1, 2, 2, 1, 3, 2 );           // 13 : [-]
		$tab128[] = array( 1, 2, 2, 2, 3, 1 );           // 14 : [.]
		$tab128[] = array( 1, 1, 3, 2, 2, 2 );           // 15 : [/]
		$tab128[] = array( 1, 2, 3, 1, 2, 2 );           // 16 : [0]
		$tab128[] = array( 1, 2, 3, 2, 2, 1 );           // 17 : [1]
		$tab128[] = array( 2, 2, 3, 2, 1, 1 );           // 18 : [2]
		$tab128[] = array( 2, 2, 1, 1, 3, 2 );           // 19 : [3]
		$tab128[] = array( 2, 2, 1, 2, 3, 1 );           // 20 : [4]
		$tab128[] = array( 2, 1, 3, 2, 1, 2 );           // 21 : [5]
		$tab128[] = array( 2, 2, 3, 1, 1, 2 );           // 22 : [6]
		$tab128[] = array( 3, 1, 2, 1, 3, 1 );           // 23 : [7]
		$tab128[] = array( 3, 1, 1, 2, 2, 2 );           // 24 : [8]
		$tab128[] = array( 3, 2, 1, 1, 2, 2 );           // 25 : [9]
		$tab128[] = array( 3, 2, 1, 2, 2, 1 );           // 26 : [:]
		$tab128[] = array( 3, 1, 2, 2, 1, 2 );           // 27 : [;]
		$tab128[] = array( 3, 2, 2, 1, 1, 2 );           // 28 : [<]
		$tab128[] = array( 3, 2, 2, 2, 1, 1 );           // 29 : [=]
		$tab128[] = array( 2, 1, 2, 1, 2, 3 );           // 30 : [>]
		$tab128[] = array( 2, 1, 2, 3, 2, 1 );           // 31 : [?]
		$tab128[] = array( 2, 3, 2, 1, 2, 1 );           // 32 : [@]
		$tab128[] = array( 1, 1, 1, 3, 2, 3 );           // 33 : [A]
		$tab128[] = array( 1, 3, 1, 1, 2, 3 );           // 34 : [B]
		$tab128[] = array( 1, 3, 1, 3, 2, 1 );           // 35 : [C]
		$tab128[] = array( 1, 1, 2, 3, 1, 3 );           // 36 : [D]
		$tab128[] = array( 1, 3, 2, 1, 1, 3 );           // 37 : [E]
		$tab128[] = array( 1, 3, 2, 3, 1, 1 );           // 38 : [F]
		$tab128[] = array( 2, 1, 1, 3, 1, 3 );           // 39 : [G]
		$tab128[] = array( 2, 3, 1, 1, 1, 3 );           // 40 : [H]
		$tab128[] = array( 2, 3, 1, 3, 1, 1 );           // 41 : [I]
		$tab128[] = array( 1, 1, 2, 1, 3, 3 );           // 42 : [J]
		$tab128[] = array( 1, 1, 2, 3, 3, 1 );           // 43 : [K]
		$tab128[] = array( 1, 3, 2, 1, 3, 1 );           // 44 : [L]
		$tab128[] = array( 1, 1, 3, 1, 2, 3 );           // 45 : [M]
		$tab128[] = array( 1, 1, 3, 3, 2, 1 );           // 46 : [N]
		$tab128[] = array( 1, 3, 3, 1, 2, 1 );           // 47 : [O]
		$tab128[] = array( 3, 1, 3, 1, 2, 1 );           // 48 : [P]
		$tab128[] = array( 2, 1, 1, 3, 3, 1 );           // 49 : [Q]
		$tab128[] = array( 2, 3, 1, 1, 3, 1 );           // 50 : [R]
		$tab128[] = array( 2, 1, 3, 1, 1, 3 );           // 51 : [S]
		$tab128[] = array( 2, 1, 3, 3, 1, 1 );           // 52 : [T]
		$tab128[] = array( 2, 1, 3, 1, 3, 1 );           // 53 : [U]
		$tab128[] = array( 3, 1, 1, 1, 2, 3 );           // 54 : [V]
		$tab128[] = array( 3, 1, 1, 3, 2, 1 );           // 55 : [W]
		$tab128[] = array( 3, 3, 1, 1, 2, 1 );           // 56 : [X]
		$tab128[] = array( 3, 1, 2, 1, 1, 3 );           // 57 : [Y]
		$tab128[] = array( 3, 1, 2, 3, 1, 1 );           // 58 : [Z]
		$tab128[] = array( 3, 3, 2, 1, 1, 1 );           // 59 : [[]
		$tab128[] = array( 3, 1, 4, 1, 1, 1 );           // 60 : [\]
		$tab128[] = array( 2, 2, 1, 4, 1, 1 );           // 61 : []]
		$tab128[] = array( 4, 3, 1, 1, 1, 1 );           // 62 : [^]
		$tab128[] = array( 1, 1, 1, 2, 2, 4 );           // 63 : [_]
		$tab128[] = array( 1, 1, 1, 4, 2, 2 );           // 64 : [`]
		$tab128[] = array( 1, 2, 1, 1, 2, 4 );           // 65 : [a]
		$tab128[] = array( 1, 2, 1, 4, 2, 1 );           // 66 : [b]
		$tab128[] = array( 1, 4, 1, 1, 2, 2 );           // 67 : [c]
		$tab128[] = array( 1, 4, 1, 2, 2, 1 );           // 68 : [d]
		$tab128[] = array( 1, 1, 2, 2, 1, 4 );           // 69 : [e]
		$tab128[] = array( 1, 1, 2, 4, 1, 2 );           // 70 : [f]
		$tab128[] = array( 1, 2, 2, 1, 1, 4 );           // 71 : [g]
		$tab128[] = array( 1, 2, 2, 4, 1, 1 );           // 72 : [h]
		$tab128[] = array( 1, 4, 2, 1, 1, 2 );           // 73 : [i]
		$tab128[] = array( 1, 4, 2, 2, 1, 1 );           // 74 : [j]
		$tab128[] = array( 2, 4, 1, 2, 1, 1 );           // 75 : [k]
		$tab128[] = array( 2, 2, 1, 1, 1, 4 );           // 76 : [l]
		$tab128[] = array( 4, 1, 3, 1, 1, 1 );           // 77 : [m]
		$tab128[] = array( 2, 4, 1, 1, 1, 2 );           // 78 : [n]
		$tab128[] = array( 1, 3, 4, 1, 1, 1 );           // 79 : [o]
		$tab128[] = array( 1, 1, 1, 2, 4, 2 );           // 80 : [p]
		$tab128[] = array( 1, 2, 1, 1, 4, 2 );           // 81 : [q]
		$tab128[] = array( 1, 2, 1, 2, 4, 1 );           // 82 : [r]
		$tab128[] = array( 1, 1, 4, 2, 1, 2 );           // 83 : [s]
		$tab128[] = array( 1, 2, 4, 1, 1, 2 );           // 84 : [t]
		$tab128[] = array( 1, 2, 4, 2, 1, 1 );           // 85 : [u]
		$tab128[] = array( 4, 1, 1, 2, 1, 2 );           // 86 : [v]
		$tab128[] = array( 4, 2, 1, 1, 1, 2 );           // 87 : [w]
		$tab128[] = array( 4, 2, 1, 2, 1, 1 );           // 88 : [x]
		$tab128[] = array( 2, 1, 2, 1, 4, 1 );           // 89 : [y]
		$tab128[] = array( 2, 1, 4, 1, 2, 1 );           // 90 : [z]
		$tab128[] = array( 4, 1, 2, 1, 2, 1 );           // 91 : [{]
		$tab128[] = array( 1, 1, 1, 1, 4, 3 );           // 92 : [|]
		$tab128[] = array( 1, 1, 1, 3, 4, 1 );           // 93 : [}]
		$tab128[] = array( 1, 3, 1, 1, 4, 1 );           // 94 : [~]
		$tab128[] = array( 1, 1, 4, 1, 1, 3 );           // 95 : [DEL]
		$tab128[] = array( 1, 1, 4, 3, 1, 1 );           // 96 : [FNC3]
		$tab128[] = array( 4, 1, 1, 1, 1, 3 );           // 97 : [FNC2]
		$tab128[] = array( 4, 1, 1, 3, 1, 1 );           // 98 : [SHIFT]
		$tab128[] = array( 1, 1, 3, 1, 4, 1 );           // 99 : [Cswap]
		$tab128[] = array( 1, 1, 4, 1, 3, 1 );           // 100 : [Bswap]
		$tab128[] = array( 3, 1, 1, 1, 4, 1 );           // 101 : [Aswap]
		$tab128[] = array( 4, 1, 1, 1, 3, 1 );           // 102 : [FNC1]
		$tab128[] = array( 2, 1, 1, 4, 1, 2 );           // 103 : [Astart]
		$tab128[] = array( 2, 1, 1, 2, 1, 4 );           // 104 : [Bstart]
		$tab128[] = array( 2, 1, 1, 2, 3, 2 );           // 105 : [Cstart]
		$tab128[] = array( 2, 3, 3, 1, 1, 1 );           // 106 : [STOP]
		$tab128[] = array( 2, 1 );                       // 107 : [END BAR]

		for ( $i = 32; $i <= 95; $i++ ) {
			$ABCset .= chr( $i );
		}
		$Aset = $ABCset;
		$Bset = $ABCset;
		for ( $i = 0; $i <= 31; $i++ ) {
			$ABCset .= chr( $i );
			$Aset .= chr( $i );
		}
		for ( $i = 96; $i <= 127; $i++ ) {
			$ABCset .= chr( $i );
			$Bset .= chr( $i );
		}
		for ( $i = 200; $i <= 210; $i++ ) {
			$ABCset .= chr( $i );
			$Aset .= chr( $i );
			$Bset .= chr( $i );
		}
		$Cset = '0123456789' . chr( 206 );
		for ( $i = 0; $i < 96; $i++ ) {
			$SetFrom['A'] .= chr( $i );
			$SetFrom['B'] .= chr( $i + 32 );
			$SetTo['A'] .= chr( ( $i < 32 ) ? $i + 64 : $i - 32 );
			$SetTo['B'] .= chr( $i );
		}
		for ( $i = 96; $i < 107; $i++ ) {
			$SetFrom['A'] .= chr( $i + 104 );
			$SetFrom['B'] .= chr( $i + 104 );
			$SetTo['A'] .= chr( $i );
			$SetTo['B'] .= chr( $i );
		}
		$Aguid = '';
		$Bguid = '';
		$Cguid = '';
		for ( $i = 0; $i < strlen( $code ); $i++ ) {
			$needle = substr( $code, $i, 1 );
			$Aguid .= ( ( strpos( $Aset, $needle ) === false ) ? 'N' : 'O' );
			$Bguid .= ( ( strpos( $Bset, $needle ) === false ) ? 'N' : 'O' );
			$Cguid .= ( ( strpos( $Cset, $needle ) === false ) ? 'N' : 'O' );
		}
		$SminiC = 'OOOO';
		$IminiC = 4;
		$crypt = '';
		while ( $code > '' ) {
			$i = strpos( $Cguid, $SminiC );
			if ( $i !== false ) {
				$Aguid [ $i ] = 'N';
				$Bguid [ $i ] = 'N';
			}
			if ( substr( $Cguid, 0, $IminiC ) == $SminiC ) {
				$crypt .= chr( ( $crypt > '' ) ? $JSwap['C'] : $JStart['C'] );
				$made = strpos( $Cguid, 'N' );
				if ( $made === false ) {
					$made = strlen( $Cguid );
				}
				if ( fmod( $made, 2 ) == 1 ) {
					$made--;
				}
				for ( $i = 0; $i < $made; $i += 2 ) {
					$crypt .= chr( strval( substr( $code, $i, 2 ) ) );
				}
				$jeu = 'C';
			} else {
				$madeA = strpos( $Aguid, 'N' );
				if ( $madeA === false ) {
					$madeA = strlen( $Aguid );
				}
				$madeB = strpos( $Bguid, 'N' );
				if ( $madeB === false ) {
					$madeB = strlen( $Bguid );
				}
				$made = ( ( $madeA < $madeB ) ? $madeB : $madeA );
				$jeu = ( ( $madeA < $madeB ) ? 'B' : 'A' );
				$crypt .= chr( ( $crypt > '' ) ? $JSwap[ $jeu ] : $JStart[ $jeu ] );
				$crypt .= strtr( substr( $code, 0, $made ), $SetFrom[ $jeu ], $SetTo[ $jeu ] );
			}
			$code = substr( $code, $made );
			$Aguid = substr( $Aguid, $made );
			$Bguid = substr( $Bguid, $made );
			$Cguid = substr( $Cguid, $made );
		}
		$check = ord( $crypt[0] );
		for ( $i = 0; $i < strlen( $crypt ); $i++ ) {
			$check += ( ord( $crypt[ $i ] ) * $i );
		}
		$check %= 103;
		$crypt .= chr( $check ) . chr( 106 ) . chr( 107 );
		$i = ( strlen( $crypt ) * 11 ) - 8;
		$modul = $w / $i;
		for ( $i = 0; $i < strlen( $crypt ); $i++ ) {
			$c = $tab128[ ord( $crypt[ $i ] ) ];
			for ( $j = 0; $j < count( $c ); $j++ ) {
				$pdf->Rect( $x, $y, $c[ $j ] * $modul, $h, 'F' );
				$x += ( $c[ $j++ ] + $c[ $j ] ) * $modul;
			}
		}
	}

	public static function cdi_QrCode_pdf( $pdf, $x, $y, $code, $w, $h ) {
		$writer = new PngWriter();
		// Create QR code
		$qrCode = QrCode::create( $code )
		->setEncoding( new Encoding( 'UTF-8' ) )
		->setErrorCorrectionLevel( new ErrorCorrectionLevelLow() )
		->setSize( 300 )
		->setMargin( 10 )
		->setRoundBlockSizeMode( new RoundBlockSizeModeMargin() )
		->setForegroundColor( new Color( 0, 0, 0 ) )
		->setBackgroundColor( new Color( 255, 255, 255 ) );
		$result = $writer->write( $qrCode );
		$dataUri = $result->getDataUri();
		$pdf->Image( $dataUri, $x, $y, $w, $h, 'PNG' );
	}

} // End class

