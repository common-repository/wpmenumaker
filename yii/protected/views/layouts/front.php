<div id="wpmm">
    <div id="wpmm-menus" class="<?php echo $this->menus_class ?>">
        <?php echo $content ?>
    </div>
    <div id="wpmm-form" class="<?php echo $this->form_class ?>">
            <?php $this->renderPartial('_form') ?>
    </div>
    <div id="wpmm-item-details"></div>
    <div id="wpmm-item-loading"></div>
    <?php if(MMSettingsForm::getParam('enable_social')){ ?>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <?php } ?>
</div>
<?php if(MMSettingsForm::getParam('powered_by')){ ?>
<div align="right"><a href="http://www.wpmenumaker.com/" class="wpmm-powered"></a></div>
<?php } ?>
<script>
    function buttonInit(){
        jQuery('#wpmm-form-showbutton').click(function(){
            var cart = jQuery('#wpmm-form');
            jQuery('#wpmm-form').attr('data-hide',0);
            jQuery('#wpmm-form-showbutton').remove();
            if (jQuery('#wpmm-form').hasClass('outside-right')){
                cart.animate({right: '0px'});
            }
            if (jQuery('#wpmm-form').hasClass('outside-left')){
                cart.animate({left: '0px'});
            }
            return false;
        });
    }

    function AddressControlsInit(){
        jQuery("#wpmm-add-address").on('click', function() {
            var details =jQuery('#wpmm-item-details');
            var ddlValue = jQuery("#wpmm-address-dropdown option:selected").val();
            var id=0;
            if(ddlValue){
                id=ddlValue;
            }
            jQuery.ajax({
                url: '<?php echo MM_AJAX_URL ?>',
                data: {
                    'action': 'front',
                    'add_address': id,
                    '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
                },
                type: "POST",
                dataType: "html",
                beforeSend: function() {
                    details.hide();
                    jQuery('#wpmm-item-loading').css({'top':'50%','left':'50%','margin-left':'-'+24+'px'}).show();
                },
                error: function(){
                    jQuery('#wpmm-item-loading').css({'background-color': 'red'});
                    jQuery('#wpmm-item-loading').hide(50);
                },
                success: function(data) {
                    if (data) {
                        jQuery('#wpmm-item-loading').hide();
                        details.html(data);
                        details.css({'top':'50%','left':'50%','margin':'-'+(details.height()-150)+'px'+' 0 0 -'+(details.width() / 2)+'px'});
                    }
                    details.show();
                }
            });
            return false;
        });
    }

    function CalculateFromScroll(){
         if (jQuery('#wpmm-form').hasClass('outside-left') || jQuery('#wpmm-form').hasClass('outside-right')){
            var sumheight=jQuery('#wpmm-forn-hide-container').height()+jQuery('#wpmm-cart-control').height();
            sumheight= jQuery('#wpmm-form').height()-sumheight;
            jQuery('#wpmm-form-container-scroll').css({'height':sumheight+'px','overflow':'hidden'});
         }
    }

    function InitCart(){
        AddressControlsInit();
        if (jQuery('#wpmm-form').hasClass('outside-left') || jQuery('#wpmm-form').hasClass('outside-right')){
            var cart=jQuery('#wpmm-form');
            cart.prepend('<div id="wpmm-forn-hide-container"><a class="wpmmcart-hide" href="#">Hide</a><hr class="wpmm-form-hr"></div>');
            jQuery('#wpmm-form').attr('data-hide',0);
            jQuery('#wpmm-cart-control').prepend('<hr class="wpmm-form-hr">').css({'position':'absolute','bottom':'0', 'width':'100%', 'height':'60px','display':'block','zIndex':'5'});
            jQuery(document).ready(function(){
                CalculateFromScroll();
                jQuery("#wpmm-form-container-scroll").mCustomScrollbar();
                if(jQuery(window).width() <= 1280 && jQuery('#wpmm-form').attr('data-hide') == 0){
                    jQuery('.wpmmcart-hide').trigger('click');
                }
            }
        );

        jQuery('.wpmmcart-hide').click(function(){
             var cart = jQuery('#wpmm-form');
             jQuery('#wpmm-form').attr('data-hide',1);
             if (jQuery('#wpmm-form').hasClass('outside-right')){
                 cart.animate({right: '-' + jQuery('#wpmm-form').width()  + 'px'},function(){
                     if(jQuery('#wpmm-form-showbutton').length == 0){
                         jQuery('#wpmm').append('<div id="wpmm-form-showbutton" align="center"><div>ORDER</div></div>');
                         jQuery('#wpmm-form-showbutton').css({'right': '0'});
                         buttonInit();
                     }
                 });
             }
             if (jQuery('#wpmm-form').hasClass('outside-left')){
                 cart.animate({left: '-' + jQuery('#wpmm-form').width()  + 'px'},function(){
                     if(jQuery('#wpmm-form-showbutton').length == 0){
                         jQuery('#wpmm').append('<div id="wpmm-form-showbutton" align="center"><div>ORDER</div></div>');
                         jQuery('#wpmm-form-showbutton').css({'left': '0'});
                         buttonInit();
                     }
                 });
             }
             return false;
         });
    }
        jQuery(window).resize(function() {
            CalculateFromScroll();
            var vwidth=jQuery(window).width();
            if(vwidth <= 1280 && jQuery('#wpmm-form').attr('data-hide') == 0){
                jQuery('.wpmmcart-hide').trigger('click');
            } else if (jQuery('#wpmm-form').attr('data-hide') == 1 && vwidth >= 1280) {
                jQuery('#wpmm-form-showbutton').trigger('click');
            }
        });
    }
    jQuery("a[rel=wpmm-lightbox]").fancybox({'titlePosition'	: 'over'});
    jQuery("a[rel=wpmm-lightbox] img").hover(function() {
        jQuery(this).stop().animate({"opacity": 0.8});
    },function() {
        jQuery(this).stop().animate({"opacity": 1});
    });
    function fixHeight() {
        if (jQuery("#wpmm-form").hasClass("inside-left") || jQuery("#wpmm-form").hasClass("inside-right")) {
            var effect = '<?php echo MMSettingsForm::getParam('tabs_effect') ?>';
            jQuery("#wpmm").css('minHeight', jQuery('#wpmm-form').outerHeight());
        }
    }
    fixHeight();
    function setupFade() {
        var effect = '<?php echo MMSettingsForm::getParam('tabs_effect') ?>';
        jQuery('#wpmm-fade-container').css({'position': 'relative'});
        if (effect === 'fade') {
            jQuery('.wpmm-item-description').mCustomScrollbar();
        }
    }
    function adjustslide(){
        var effect = '<?php echo MMSettingsForm::getParam('tabs_effect') ?>';
        if(effect == 'slide'){
            jQuery('#wpmm-slider').css({'width': (jQuery('#wpmm-slide-container').width()*jQuery('.wpmm-tab-content.visible').length+100)});
            jQuery('.wpmm-tab-content.visible').css({'width': jQuery('#wpmm-slide-container').width()-10});
            var animWidth= jQuery('#wpmm-slide-container').width();
            jQuery("#wpmm-menu-list a").each(function(){
                if(jQuery(this).hasClass('active')){
                    var positiontab=jQuery(this).attr('data-num');
                    jQuery('#wpmm-slider').css({left: '-' + animWidth*positiontab +'px'});
                    positiontab++;
                    jQuery('#wpmm-slide-container').css({'overflow-y':'hidden', 'height': jQuery('#wpmm-tab-'+positiontab).height()});
                }
            });
        }
    }
    function ajustfade(){
        var effect = '<?php echo MMSettingsForm::getParam('tabs_effect') ?>';
        if(effect == 'fade'){
            jQuery("#wpmm-menu-list a").each(function(){
                if(jQuery(this).hasClass('active')){
                    var heightofdiv = jQuery(jQuery(this).attr('href')).height();
                    jQuery('#wpmm-fade-container').css({'height':heightofdiv});
             }
            });
        }
    }
    jQuery(document).ready(function(){
        jQuery(window).resize(function() {
            adjustslide();
            ajustfade();
        });
        var effect = '<?php echo MMSettingsForm::getParam('tabs_effect') ?>';
        if(effect !='fade' && effect !='scale'){
            jQuery('.wpmm-item-description').mCustomScrollbar();
            setTimeout(function(){
                jQuery('#wpmm-slide-container').css({'overflow-y':'hidden', 'height': jQuery('#wpmm-tab-1').height()});
            },2000);
        }else if(effect == 'scale'){
            jQuery('.wpmm-tab-content').each(function(){
                jQuery(this).addClass('visible');
            });
            jQuery('.wpmm-item-description').mCustomScrollbar();
            jQuery('.wpmm-tab-content').each(function(){
                jQuery(this).removeClass('visible');
            });
            jQuery('#wpmm-tab-1').addClass('visible');
        }
        adjustslide();
        setupFade();
        InitCart();
        <?php if(isset($_GET['wpmm-id'])) {?>
            jQuery(document).ready(function() {
                setTimeout(function() {
                       jQuery( ".wpmm-item[data-id=<?php echo $_GET['wpmm-id']; ?>]").first().click();
                   }, 5000);
            });
        <?php } ?>
    });

    jQuery("#wpmm-menu-list-vert a").on('click', function() {
        var target=jQuery(this).attr('href');
        jQuery('html, body').animate({ scrollTop: jQuery(target).offset().top-50 }, 1000, function(){
            jQuery(target+' .wpmm-menu-image-wrap').animate({opacity:0.4}, 50,function(){
                jQuery(target+' .wpmm-menu-image-wrap').animate({opacity:0.8}, 50,function(){
                    jQuery(target+' .wpmm-menu-image-wrap').animate({opacity:1},50);
                });
            });
        });
        return false;
    });

    jQuery("#wpmm-menu-list a").on('click', function() {
        if (jQuery(this).hasClass('active') || jQuery(".wpmm-tab-content.loading").length > 0)
            return false;
        var effect = '<?php echo MMSettingsForm::getParam('tabs_effect') ?>';
        jQuery('.wpmm-item-details').hide();
        var clicked = jQuery(this).attr('href');
        var currenttab = null;
        jQuery("#wpmm-menu-list a").each(function() {
            if(jQuery(this).hasClass('active')){
                currenttab=jQuery(this).attr('href');
            }
            jQuery(this).removeClass();
        });
        jQuery(this).attr('class', 'active');
        switch (effect) {
            default :
            case 'fade' :
                jQuery(clicked).show();
                jQuery(currenttab).animate({ opacity: 0.0 },200,function(){
                    jQuery(clicked).animate({ opacity: 1.0 },200,function(){
                        jQuery('#wpmm-fade-container').css({'position':'relative','height':jQuery(clicked).height()});
                        jQuery(currenttab).hide();
                    });
                });
                break;
            case 'slide' :
                var animWidth= jQuery('#wpmm-slide-container').width();
                var positiontab=jQuery(this).attr('data-num');
                jQuery('#wpmm-slider').stop().animate({left: '-' + animWidth*positiontab +'px'}, 500, function(){
                    positiontab++;
                    jQuery('#wpmm-slide-container').css({'overflow-y':'hidden', 'height': jQuery('#wpmm-tab-'+positiontab).height()});
                });
                break;
            case 'scale' :
                var formheight = jQuery('#wpmm-form').height();
                var currentheight = jQuery(currenttab).height();
                var clickedheight = jQuery(clicked).height();
                if (jQuery('#wpmm-tab-content-wrap').length)
                    jQuery('#wpmm-tab-content-wrap').css('height', Math.max(formheight, currentheight, clickedheight) + 'px');
                else
                    jQuery('#wpmm-fade-container').wrapInner('<div id="wpmm-tab-content-wrap" style="height:' + currentheight + 'px" />');
                jQuery('.wpmm-tab-content.visible').hide('slow', function() {
                    jQuery(clicked).attr({'class': 'wpmm-tab-content visible'}).show('slow', function() {
                        jQuery(this).attr({'class': 'wpmm-tab-content visible', 'style': 'display:block'});
                    });
                    jQuery(this).attr({'class': 'wpmm-tab-content', 'style': 'display:none'});
                });
                jQuery('html, body').animate({ scrollTop: jQuery("#wpmm").offset().top-40 }, 1000);
                if (jQuery('#wpmm-form').hasClass('inside-left') || jQuery('#wpmm-form').hasClass('inside-right'))
                    jQuery('#wpmm-tab-content-wrap').css('height', Math.max(formheight, clickedheight) + 'px');
                else
                    jQuery('#wpmm-tab-content-wrap').css('height', clickedheight + 'px');
                break;
        }
        jQuery('#wpmm').trigger('resize');
        return false;
    });
    jQuery('.wpmm-item-buy, .wpmm-item').on('click', function() {
        var details = jQuery('#wpmm-item-details');
        var id = jQuery(this).attr('data-id');
        jQuery.ajax({
            url: '<?php echo MM_AJAX_URL ?>',
            data: {
                'action': 'front',
                'itemdetails': {'id': id},
                '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
            },
            type: "POST",
            dataType: "html",
            beforeSend: function() {
                details.hide();
                jQuery('#wpmm-item-loading').css({'top':'50%','left':'50%','margin-left':'-'+24+'px'}).show();

            },
            error: function(){
                jQuery('#wpmm-item-loading').css({'background-color': 'red'});
                jQuery('#wpmm-item-loading').hide(50);
            },
            success: function(data) {
                details.show();
                if (data) {
                    jQuery('#wpmm-item-loading').hide();
                    details.html(data);
                    details.attr('data-id', id);
                    details.css({'top':'50%','left':'50%','margin':'-'+(details.height()-200)+'px'+' 0 0 -'+(details.width() / 2)+'px'});
                    refresh_item_total();
                }
            }
        });
        return false;
    }).children(':not(.wpmm-item-header)').click(function() {
        return false;
    });
    function refresh_item_total() {
        var price = parseFloat(jQuery('#wpmm-item-details-price span').attr('data-price'));
        var quantity = parseInt(jQuery('#wpmm-item-details-quantity').text());
        jQuery("#wpmm-item-details-attributes li input:checked").each(function(){
            price += parseFloat(jQuery(this).attr('data-price'));
        });
        jQuery('#wpmm-item-details-price span').text((price*quantity).toFixed(2));
        jQuery('#wpmm-item-details-price').animate({
            'font-size' : '24px'
        }, 150).animate({
            'font-size' : '18px'
        }, 150);
    }
    function refresh_order(form) {
        if (typeof form === 'undefined' && form !== false) {
            form = true;
        }
        var params = {};
        if (form) {
            jQuery.each(jQuery('#wpmm-form').children().serializeArray(), function(index, value) {
                if(value.name == 'order-time'){
                    if(jQuery('#wpmm-today').is(':checked')){
                        params[value.name] = jQuery('#wpmm-form-today-time').val();
                        jQuery('#wpmm-form-date-time').val('');
                    } else if(jQuery('#wpmm-date').is(':checked')) {
                        params[value.name] = jQuery('#wpmm-form-date-time').val();
                        jQuery('#wpmm-form-today-time').val('');
                    }
                 } else {
                    params[value.name] = value.value;
                 }
            });
        }
        jQuery.ajax({
            url: '<?php echo MM_AJAX_URL ?>',
            data: {
                'action': 'front',
                'refreshorder': true,
                'data': params,
                '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
            },
            type: "POST",
            dataType: "html",
            beforeSend: function() {
                jQuery('#wpmm-item-loading').css({'top':'50%','left':'50%','margin-left':'-'+24+'px'}).show();
            },
            success: function(data) {
                if (data) {
                    jQuery('#wpmm-form').html(data);
                    InitCart();
                    jQuery('#wpmm-item-loading').hide();
                }
            }
        });
    }
    function checkoutOrder(){
    var details = jQuery('#wpmm-item-details');
        jQuery.ajax({
            url: '<?php echo MM_AJAX_URL ?>',
            data: {
                'action': 'front',
                'checkout': true,
                '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
            },
            type: "POST",
            dataType: "html",
            success: function(data) {
                jQuery('#wpmm-item-loading').hide();
                details.hide();
                    if(data != 'There where errors'){
                        details.html(data);
                        details.css({'top':'50%','left':'50%','margin':'-'+(details.height()-200)+'px'+' 0 0 -'+(details.width() / 2)+'px'});
                        refresh_order(false);
                        details.show();
                    } else {
                        refresh_order();
                    }
            }
        });
    }
    function checkoutOrderCard(){
    var details = jQuery('#wpmm-item-details');
    var form = {};
    form['card_number'] = jQuery('#card_number').val();
    form['exp_date'] = jQuery('#exp_date').val();
    form['orderamount'] = jQuery('#wpmm-orderamount').val();
    form['payment_vendor'] = jQuery('input[name=payment_vendor]:checked').val();
    form['p_first_name'] = jQuery('#p_first_name').val();
    form['p_last_name'] = jQuery('#p_last_name').val();
    form['p_billing_address'] = jQuery('#p_billing_address').val();
    form['p_billing_country'] = jQuery('#p_billing_country').val();
    form['p_billing_state'] = jQuery('#p_billing_state').val();
    form['p_billing_zip'] = jQuery('#p_billing_zip').val();
    form['p_card_number'] = jQuery('#p_card_number').val();
    form['p_billing_city'] = jQuery('#p_billing_city').val();
    form['p_expiration_month'] = jQuery('#p_expiration_month').val();
    form['p_expiration_year'] = jQuery('#p_expiration_year').val();
    form['p_cv_code'] = jQuery('#p_cv_code').val();
    form['p_credit_type'] = jQuery('#p_credit_type').val();
        jQuery.ajax({
            url: '<?php echo MM_AJAX_URL ?>',
            data: {
                'action': 'front',
                'checkout': true,
                'card_form': form,
                '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
            },
            type: "POST",
            dataType: "html",
            success: function(data) {
                jQuery('#wpmm-item-loading').hide();
                details.hide();
                    if(data){
                        details.html(data);
                        details.css({'top':'50%','left':'50%','margin':'-'+(details.height()-200)+'px'+' 0 0 -'+(details.width() / 2)+'px'});
                        refresh_order();
                        details.show();
                    }
            }
        });
    }
    function continueOrder(total){
    var details = jQuery('#wpmm-item-details');
        jQuery.ajax({
            url: '<?php echo MM_AJAX_URL ?>',
            data: {
                'action': 'front',
                'continueorder': true,
                'orderammount': total,
                '<?php echo Yii::app()->request->csrfTokenName ?>':'<?php echo Yii::app()->request->csrfToken ?>'
            },
            type: "POST",
            dataType: "html",
            beforeSend: function() {
                details.hide();
                jQuery('#wpmm-item-loading').css({'top':'50%','left':'50%','margin-left':'-'+24+'px'}).show();
            },
            success: function(data) {
                jQuery('#wpmm-item-loading').hide();
                    if(data != 'There where errors'){
                        details.html(data);
                        details.css({'top':'50%','left':'50%','margin':'-'+(details.height()-200)+'px'+' 0 0 -'+(details.width() / 2)+'px'});
                        details.show();
                    } else {
                        refresh_order();
                    }
            }
        });
    }
</script>
