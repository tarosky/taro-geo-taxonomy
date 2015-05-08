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

    });

})(jQuery);
