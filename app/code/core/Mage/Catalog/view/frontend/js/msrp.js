/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    frontend product msrp
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*jshint browser:true jquery:true*/
(function ($) {

    var _clickForPrice = {
        helpLink: []
    };
    var _popupCloseData = {
        closeButtonId: ''
    };

    var _helpLinkData = {
        helpText: []
    };

    var _popupCartData = {
        cartData: []
    };

    var _cartData = {
        cartFormData: []
    };

    $(document).ready(function () {
        $.mage.event.trigger("mage.price.helplink", _clickForPrice);
        $.mage.event.trigger("map.popup.close", _popupCloseData);
        $.mage.event.trigger("mage.popup.whatsthislink", _helpLinkData);
        $.mage.event.trigger("map.popup.button", _popupCartData);
        $.mage.event.trigger("product.addtocart.button", _cartData);
        $.mage.event.trigger("product.updatecart.button", _cartData);

        $.each(_clickForPrice.helpLink, function (index, value) {

            $(value.popupId).on('click', function (e) {
                if(value.submitUrl){
                    location.href=value.submitUrl;
                } else {
                    $('#map-popup-heading').text(value.productName);
                    $('#map-popup-price').html($(value.realPrice));
                    $('#map-popup-msrp').html(value.msrpPrice);

                    var width = $('#map-popup').width();
                    var offsetX = e.pageX - (width / 2) + "px";
                    $('#map-popup').css({left: offsetX, top: e.pageY}).show();
                    $('#map-popup-content').show();
                    $('#map-popup-text').addClass('map-popup-only-text').show();
                    $('#map-popup-text-what-this').hide();
                    return false;
                }
            });

        });

        $.each(_helpLinkData.helpText, function (index, value) {
            $(value.helpLinkId).on('click', function (e) {
                $('#map-popup-heading').text(value.productName);
                var width = $('#map-popup').width();
                var offsetX = e.pageX - (width / 2) + "px";
                $('#map-popup').css({left: offsetX, top: e.pageY}).show();
                $('#map-popup-content').hide();
                $('#map-popup-text').hide();
                $('#map-popup-text-what-this').show();
                return false;
            });
        });

        $(_popupCloseData.closeButtonId).on('click', function () {
            $('#map-popup').hide();
            return false;
        });

        $.each($.merge(_cartData.cartFormData, _popupCartData.cartData), function (index, value) {
            $(value.cartButtonId).on('click', function () {

                if(value.cartForm){
                    $(value.cartForm).mage().validate({
                        errorPlacement: function (error, element) {
                            if (element.is(':radio') || element.is(':checkbox')) {
                                element.closest('ul').after(error);
                            } else {
                                element.after(error);
                            }
                        },
                        highlight: function (element) {
                            if ($(element).is(':radio') || $(element).is(':checkbox')) {
                                $(element).closest('ul').addClass('mage-error');
                            } else {
                                $(element).addClass('mage-error');
                            }
                        },
                        unhighlight: function (element) {
                            if ($(element).is(':radio') || $(element).is(':checkbox')) {
                                $(element).closest('ul').removeClass('mage-error');
                            } else {
                                $(element).removeClass('mage-error');
                            }
                        }
                    });
                }
                if(value.addToCartUrl) {
                    if($('#map-popup')){
                        $('#map-popup').hide();
                    }
                    if(opener !== null){
                        opener.location.href=value.addToCartUrl;
                    } else {
                        location.href=value.addToCartUrl;
                    }

                }else if(value.cartForm){
                    $(value.cartForm).submit();
                }

            });
        });

    });

})(jQuery);

