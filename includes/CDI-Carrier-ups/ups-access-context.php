<?php

	$upsclientidoauth = get_option( 'cdi_o_settings_ups_clientid_oauth' ) ;
	$upsclientsecretoauth = get_option( 'cdi_o_settings_ups_clientsecret_oauth' ) ;
	$upscomptenumber = get_option( 'cdi_o_settings_ups_comptenumber_oauth' );	
	
if ( get_option( 'cdi_o_settings_ups_modetestprod' ) == 'yes' ) {
	// Prod
	$urlshipment = 'https://onlinetools.ups.com/api/shipments/v2403/ship';
	$urlvoid = 'https://onlinetools.ups.com/api/shipments/v2403/void/cancel/';	
	$urllocator = 'https://onlinetools.ups.com/api/locations/v2/search/availabilities/56';
	$urltrack = 'https://onlinetools.ups.com/api/track/v1/details/';	
	$upsurltoken = 'https://onlinetools.ups.com/security/v1/oauth/token';
} else {
	// Test
	$urlshipment = 'https://wwwcie.ups.com/api/shipments/v2403/ship';
	$urlvoid = 'https://wwwcie.ups.com/api/shipments/v2403/void/cancel/';
	$urllocator = 'https://wwwcie.ups.com/api/locations/v2/search/availabilities/56'; // Locator seems not really work in CIE mode ?
	$urltrack = 'https://wwwcie.ups.com/api/track/v1/details/';	
	$upsurltoken = 'https://wwwcie.ups.com/security/v1/oauth/token';
}

