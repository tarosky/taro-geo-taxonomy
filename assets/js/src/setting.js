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
                $.post($form.attr('action'), {
                    _wpnonce: $form.find('input[name=_wpnonce]').val(),
                    action: $form.find('input[name=action]').val(),
                    step: $form.find('input[name=step]').val(),
                    rows: $form.find('input[name=rows]').val()
                }).done(function(result){
                    $pre.html(result.message);
                    $form.find("input[name=step]").val(result.next);
                    $form.find('input[name=rows]').val(result.rows);
                }).fail(function(xhr, status, msg){
                    window.alert(msg);
                    $form.find('input[name=rows]').val(0);
                }).always(function(){
                    $form.removeClass('loading');
                    $form.addClass('loaded');
                    $form.find('pre').effect('highlight');
                    if( parseInt($form.find('input[name=step]').val(), 10) > 1 ){
                        $form.submit();
                    }
                });
            }
        });
    });


})(jQuery);
