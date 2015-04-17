/**
 * Description
 */

/* global TaroGeoVars:true */

(function ($) {
    'use strict';

    // Form aciton
    $(document).ready(function(){
        $('#taro-geo-import-form').submit(function(e){
            e.preventDefault();
            var $form = $(this),
                $pre = $form.find('pre');
            if( !$form.hasClass('loading') ){
                $form.addClass('loading');
                $pre.html(TaroGeoVars.loading);
                $.post($form.attr('action'), {
                    _wpnonce: $form.find('input[name=_wpnonce]').val(),
                    action: $form.find('input[name=action]').val()
                }).done(function(result){
                    $pre.html(result.message);
                }).fail(function(xhr, status, msg){
                    window.alert(msg);
                }).always(function(){
                    $form.removeClass('loading');
                    $form.addClass('loaded');
                    $form.effect('highlight');
                });
            }
        });
    });


})(jQuery);
