//
// Global : cdilistsites  cdiparammaplon   cdiparammaplat cdiparammapz .
//
jQuery( document ).ready(
	function() {
		jQuery( '#cdimapcontainer' ).each(
			function() {
				var sites = cdilistsites;
				var listFeatures = [];
				var length = sites.length;
				for (var i = 0; i < length; i++) {
					var site = sites[i];
					var iconFeature = new ol.Feature(
						{
							geometry: new ol.geom.Point( ol.proj.fromLonLat( [site[1], site[0]] ), ),
							name: '<div style=\"display:inline-block; height:200px ; width:260px; overflow:scroll; z-index:1; \">' + site[2] + '</div><button id=\"close\" style=\"float:right; padding:5px; border:1px solid black; border-radius:5px; \" onclick=\"jQuery(document.getElementById(\'cdiompopup\')).hide(); return false;\">X</button>',
						}
					);
					if (site[3] != null) {
						iconFeature.setStyle(
							new ol.style.Style(
								{
									image: new ol.style.Icon(
										{
											anchor: [0.5, 0.5],
											anchorXUnits: 'fraction',
											anchorYUnits: 'fraction',
											src: site[3],
										}
									)
								}
							)
						);
						listFeatures.push( iconFeature );
					}
				}
				var iconLayerSource = new ol.source.Vector(
					{
						features: listFeatures,
					}
				);
				var iconLayer = new ol.layer.Vector(
					{
						source: iconLayerSource,
					}
				);
				var map = new ol.Map(
					{
						target: 'mapom',
						layers: [
						new ol.layer.Tile(
							{
								source: new ol.source.OSM()
							}
						),
					iconLayer,
					],
					view: new ol.View(
						{
							center: ol.proj.fromLonLat( [cdiparammaplon, cdiparammaplat] ),
							zoom: cdiparammapz
						}
					)
					}
				);
				jQuery( '#cdiompopup' ).each(
					function(index) {
						jQuery( this ).remove();
					}
				);
				var createcdiompopup = document.createElement( 'div' );
				createcdiompopup.id = 'cdiompopup';
				document.body.appendChild( createcdiompopup );
				jQuery( document ).ready(
					function(seachpage) {
						var pagex;
						var pagey;
						jQuery( document ).on(
							'mousemove',
							function(mulot) {
								pagex = mulot.pageX;
								pagey = mulot.pageY;
							}
						);
						jQuery( document ).on(
							'click',
							function(brosswagon) {
								function cleaning() {
									var isitmapom = document.getElementById( 'mapom' );
									if ( ! isitmapom) {
										jQuery( '#cdiompopup' ).each(
											function(index) {
												jQuery( this ).remove();
											}
										);
									}
								}
								setTimeout( cleaning, 2000 );
							}
						);
						map.on(
							'click',
							function(evt) {
								var element = document.getElementById( 'cdiompopup' );
								var cdiompopup = new ol.Overlay(
									{
										element: element,
										positioning: 'bottom-center',
										stopEvent: true,
										offset: [0, -50]
									}
								);
								map.addOverlay( cdiompopup );
								var feature = map.forEachFeatureAtPixel(
									evt.pixel,
									function(feature) {
										return feature;
									}
								);
								if (feature) {
									var coordinates = feature.getGeometry().getCoordinates();
									cdiompopup.setPosition( coordinates );
									var posittop = pagey - 250;
									var positleft = pagex - 144;
									document.body.appendChild( element );
									element.style = 'top:' + posittop + 'px; left:' + positleft + 'px; height:230px; position:absolute; background-color:white; padding:3px; border:1px solid black; border-radius: 5px;';
									var htmlcontent = feature.get( 'name' );
									jQuery( element ).html( '<div>' + htmlcontent + '<div style=\'margin : 0 auto; width: 0; height: 0; border-left: 10px solid transparent; border-right: 10px solid transparent; border-top: 40px solid red;\' ></div></div>' );
									jQuery( element ).show();
								}
								map.on(
									'pointermove',
									function(e) {
										if (e.dragging) {
											return;
										}
									}
								);
							}
						);
					}
				);
			}
		);
	}
);
