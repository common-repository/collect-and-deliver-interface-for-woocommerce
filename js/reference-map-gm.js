//
// Global : cdilistsites  cdiparamgooglemaplat cdiparamgooglemaplon cdiparamgooglemapz cdiparamgooglemapmaptype cdiparamgooglemapstyles cdiparamgooglemapid .
//
jQuery( document ).ready(
	function() {
		jQuery( '#cdimapcontainer' ).each(
			function() {
				if (cdilistsites != null) {
					var sites = cdilistsites;
					var infowindow = null;
					var latlng = new google.maps.LatLng( cdiparamgooglemaplat, cdiparamgooglemaplon );
					var myOptions = {
						zoom: cdiparamgooglemapz,
						center: latlng,
						mapTypeId: google.maps.MapTypeId.cdiparamgooglemapmaptype,
						styles: cdiparamgooglemapstyles
					};
					var thegmmap = new google.maps.Map( document.getElementById( cdiparamgooglemapid ), myOptions );
					var length = sites.length;
					for (var i = 0; i < length; i++) {
						var site = sites[i];
						var siteLatLng = new google.maps.LatLng( site[0], site[1] );
						if (site[3] != null) {
							var markerimage = site[3];
							var marker = new google.maps.Marker(
								{
									position: siteLatLng,
									map: thegmmap,
									icon: markerimage,
									html: site[2]
								}
							);
						} else {
							var marker = new google.maps.Marker(
								{
									position: siteLatLng,
									map: thegmmap,
									html: site[2]
								}
							);
						}
						var contentString = 'Some content';
						google.maps.event.addListener(
							marker,
							'click',
							function() {
								infowindow.setContent( this.html );
								infowindow.open( thegmmap, this );
							}
						);
					}
					infowindow = new google.maps.InfoWindow(
						{
							content: 'loading...'
						}
					);
				}
			}
		);
	}
);
