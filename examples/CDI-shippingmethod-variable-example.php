<?php

/**
 * This file is part of the CDI - Collect and Deliver Interface plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ABSPATH')) exit;

//
// *** Some simple examples of small php code you may process with filtering the variable area of a CDI shipping method tariff line
//
    ?><style>

    To add a fee by item in the cart :
    <?php $return = (float)$woocommerce->cart->cart_contents_count*3; ?>

    To add a percent of the cart price VAT excluded :
    <?php $return = (float)$woocommerce->cart->cart_contents_total*0.01; ?>

    To add a percent of the cart price VAT included :
    <?php $return = (float)$woocommerce->cart->subtotal*0.01; ?>

    To add a fee based on the weight of the cart :
    <?php $return = (float)$woocommerce->cart->cart_contents_weight*0.003; ?>

    To add a percent of the fixed rate based on the weight of the cart :
    <?php $level = error_reporting(0); define(fare, fare); error_reporting($level); $return = (float)$woocommerce->cart->cart_contents_weight*0.001*$rates[fare]; ?>

    To add 0.90€(VAT excluded)/150€(VAT included) of the cart price as insurance paid by merchand:
    <?php $price=$woocommerce->cart->subtotal; $assurance=ceil($price/150)*0.90; $return = $assurance; ?>

   </style><?php


?>
