/*!
 * Edit screen
 *
 * @handle taro-geo-mb
 * @deps jquery-effects-highlight, jquery-token-input, geolonia-map, wp-api-fetch, wp-i18n
 */

/* global geolonia: false */
/* global TaroGeo: true */

const $ = jQuery;
const { apiFetch } = wp;
const { __ } = wp.i18n;


$( document ).ready( function () {

	// Sync prefecture ID
	$( '#prefecture', '#taro-geo-taxonomy-box' ).change( function () {
		const $city = $( '#city', '#taro-geo-taxonomy-box' ),
			prev = $city.attr( 'data-prefecture' ),
			next = $( this ).val();
		$city.attr( 'data-prefecture', next );
		if ( prev !== next ) {
			$city.trigger( 'clear.geo.taro' );
		}
	} );

	// Token input
	const $city = $( '#city', '#taro-geo-taxonomy-box' );

	$city.tokenInput( function () {
		return TaroGeo.token + "&term_id=" + $( '#city' ).attr( 'data-prefecture' );
	}, {
		tokenLimit: 1,
		preventDuplicates: true,
		searchDelay: 500
	} );

	$city.bind( 'clear.geo.taro', function () {
		$( this ).tokenInput( 'clear' );
	} );

	// Fil token if possible
	const id = $city.attr( 'data-id' ),
		name = $city.attr( 'data-name' );
	if ( id && name ) {
		$city.tokenInput( 'add', {
			id: parseInt( id, 10 ),
			name: name
		} );
	}

	// Zip search
	$( '.taro-zip-search' ).click( function ( e ) {
		e.preventDefault();
		$.get( TaroGeo.zip, {
			zip: $( '#zip' ).val()
		} ).done( function ( address ) {
			if ( address.prefecture ) {
				let hit = false;
				$( 'option', '#prefecture' ).each( function ( index, opt ) {
					if ( $( opt ).text() === address.prefecture ) {
						$( opt ).attr( 'selected', true );
						hit = true;
					}
				} );
				if ( hit ) {
					$( '#prefecture' ).trigger( 'change' );
				}
			}
			if ( address.city ) {
				$city.tokenInput( 'add', address.city );
			}
			if ( address.street ) {
				$( '#street' ).val( address.street );
			}
		} );
	} );

	// Google Map
		const $mapContainer = $( '#geolonia-map' );

		if ( $mapContainer.length ) {

			// Cet center if exists
			let lat = $( '#lat' ).val();
			let lng = $( '#lng' ).val();
			const reg = /^[0-9\.-]+$/;
			lat = reg.test( lat ) ? parseFloat( lat ) : 35.686573;
			lng = reg.test( lng ) ? parseFloat( lng ) : 139.742216;

			// Clear address on button click.
			$( '#taro-geo-clearer' ).click( function ( e ) {
				e.preventDefault();
				$( '#lat' ).val( '' );
				$( '#lng' ).val( '' );
				$( '#address-src' ).val( 'manual' );
			} );

			// Initialize map.
			const map = new geolonia.Map( '#geolonia-map' );
			map.setZoom( 17 );
			map.panTo( [ lng, lat ] );

			// Droop marker
			const marker = new geolonia.Marker( {
				draggable: true,
			} );
			marker.setLngLat( [ lng, lat ] )
				.addTo( map );

			function setMap( latLng ) {
				map.panTo( [ latLng.lng, latLng.lat] );
			}

			function updatePosition( latLng, src = 'google' ) {
				$( '#lat' ).val( latLng.lat );
				$( '#lng' ).val( latLng.lng );
				$( '#address-src' ).val( src );
			}

			// Sync lat lng with dragging marker.
			marker.on( 'dragend', function () {
				const latlng = marker.getLngLat();
				setMap( latlng );
				updatePosition( latlng, 'manual' );
			} );
			marker.on( 'drag', function () {
				updatePosition( marker.getLngLat(), 'manual' );
			} );

			// Search Address
			$( '#taro-geo-searcher' ).click( function ( e ) {
				e.preventDefault();
				const pref = $( '#prefecture option:selected' ).text();
				const city = $( '#city' ).tokenInput( 'get' );
				const street = $( '#street' ).val();
				if ( ! pref.length || ! city.length || ! street.length ) {
					alert( __( '住所が入力されていません', 'taro-geo-tax' ) );
				}
				const address = [ pref, city[ 0 ].name.replace( /（.*）/, '' ), street ].join( ' ' );
				apiFetch( {
					path: 'taro-geo/v1/geocoding/?text=' + encodeURIComponent( address ),
				} ).then( ( res ) => {
					setMap( res );
					marker.setLngLat( [ res.lng, res.lat ] );
					updatePosition( res, 'aws' );
				} ).catch( ( res ) => {
					alert( res.message );
				} );
			} );
		}
} );

