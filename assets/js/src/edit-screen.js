/**
 * Description
 */

/*global google: true*/
/*global TaroGeo: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){

        // Sync prefecture ID
        $('#prefecture', '#taro-geo-taxonomy-box').change(function(){
            var $city = $('#city', '#taro-geo-taxonomy-box'),
                prev = $city.attr('data-prefecture'),
                next = $(this).val();
            $city.attr('data-prefecture', next);
            if( prev != next ){
                $city.trigger('clear.geo.taro');
            }
        });

        // Token input
        var $city = $('#city', '#taro-geo-taxonomy-box');

        $city.tokenInput(function(){
            return TaroGeo.token + "&term_id=" + $('#city').attr('data-prefecture');
        }, {
            tokenLimit: 1,
            preventDuplicates: true,
            searchDelay: 500
        });

        $city.bind('clear.geo.taro', function(){
            $(this).tokenInput('clear');
        });

        // Fil token if possible
        var id = $city.attr('data-id'),
            name = $city.attr('data-name');
        if( id && name ){
            $city.tokenInput('add', {
                id: parseInt(id, 10),
                name: name
            });
        }

        // Zip search
        $('.taro-zip-search').click(function(e){
            e.preventDefault();
            $.get( TaroGeo.zip, {
                zip: $('#zip').val()
            } ).done( function(address){
                if( address.prefecture ){
                    var hit = false;
                    $('option', '#prefecture').each(function(index, opt){
                        if( $(opt).text() === address.prefecture ){
                            $(opt).attr('selected', true);
                            hit = true;
                        }
                    });
                    if( hit ){
                        $('#prefecture').trigger('change');
                    }
                }
                if( address.city ){
                    $city.tokenInput('add', address.city);
                }
                if( address.street ){
                    $('#street').val( address.street );
                }
            } );
        });

        // Google Map
        (function(){
            var center = {
                    lat: 35.686573,
                    lng: 139.742216
                }, map, marker,
                $mapContainer = $('#taro-gmap-container');

            function setMap(latLng){
                center.lat = latLng.lat();
                center.lng = latLng.lng();
                marker.map.setCenter(latLng);
                marker.map.panTo(latLng);
            }

            function updatePosition(latLng){
                $('#lat').val(latLng.lat());
                $('#lng').val(latLng.lng());
            }

            if( $mapContainer.length ){

                // Cet center if exists
                var lat = $('#lat').val(),
                    lng = $('#lng').val(),
                    reg = /^[0-9\.]+$/;
                if( reg.test(lat) && reg.test(lng) ){
                    center.lat = parseFloat(lat);
                    center.lng = parseFloat(lng);
                }

                // Initialize map
                map = new google.maps.Map($mapContainer.get(0), {
                    center: new google.maps.LatLng(center.lat, center.lng),
                    zoom: 15,
                    minZoom: 11,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    zoomControlOptions: {
                        position: google.maps.ControlPosition.LEFT_CENTER
                    }
                });

                // drop marker
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(center.lat, center.lng),
                    map: map,
                    draggable:true,
                    animation: google.maps.Animation.DROP
                });

                // sync lat lng
                google.maps.event.addListener(marker, 'dragend', function(){
                    setMap(this.position);
                    updatePosition(this.position);
                });
                google.maps.event.addListener(marker, 'drag', function(){
                    updatePosition(this.position);
                });

                // Clear address
                $('#taro-geo-clearer').click(function(e){
                    e.preventDefault();
                    $('#lat').val('');
                    $('#lng').val('');
                });

                // Search Address
                var geocoder = new google.maps.Geocoder();
                $('#taro-geo-searcher').click(function(e){
                    e.preventDefault();
                    var pref = $('#prefecture option:selected').text(),
                        city = $('#city').tokenInput('get'),
                        street = $('#street').val();
                    if( pref.length && city.length && street.length ){
                        var address = [pref, city[0].name.replace(/（.*）/, ''), street].join(' ');
                        geocoder.geocode( {
                            address: address
                        }, function(results, status) {
                            console.log(address, results);
                            if ( status == google.maps.GeocoderStatus.OK ) {
                                marker.setPosition(results[0].geometry.location);
                                setMap(results[0].geometry.location);
                                updatePosition(results[0].geometry.location);
                            } else {
                                alert("住所が見つかりません: " + status);
                            }
                        });
                    }else{
                        alert('住所が入力されていません');
                    }
                });
            }

        })();
    });




})(jQuery);
