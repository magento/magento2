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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

;
(function($) {
    'use strict';

    $(document).ready(function(){
        mediaCheck({
            media: '(max-width: 640px)',

            // Switch to Mobile Version
            entry: function() {
                // minicart
                $('.action.showcart').addClass('is-disabled');

                $('.action.showcart').on( "click", function() {
                    if ($(this).hasClass('is-disabled')) {
                        window.location = $(this).attr("href");
                    }
                });

                $('.action.toggle.checkout.progress')
                    .on('click.gotoCheckoutProgress', function(e){
                        var myWrapper = '#checkout-progress-wrapper';
                        scrollTo(myWrapper + ' .title');
                        $(myWrapper + ' .title').addClass('active');
                        $(myWrapper + ' .content').show();
                    });

                $('body')
                    .on('click.checkoutProgress', '#checkout-progress-wrapper .title', function(e){
                        $(this).toggleClass('active');
                        $('#checkout-progress-wrapper .content').toggle();
                    });

                (function() {
                    var productInfoMain = $('.product.info.main'),
                        productInfoAdditional = $("#product-info-additional");

                    if (!productInfoAdditional.length) {

                        var productTitle = productInfoMain.find(".page.title.product").clone(),
                            productStock = productInfoMain.find(".stock:not(.alert)").clone();

                        productInfoAdditional = $("<div/>", {
                            id: "product-info-additional",
                            addClass: "product info additional"
                        });

                        $('.catalog-product-view .column.main')
                            .prepend(productInfoAdditional);

                        productInfoAdditional
                            .append(productTitle)
                            .append(productStock);

                    } else {
                        productInfoAdditional.removeClass("hidden");
                    }

                    productInfoMain.addClass("responsive");

                })();

                var galleryElement = $('[data-role=media-gallery]');
                if (galleryElement.length && galleryElement.data('zoom')) {
                    galleryElement.zoom('disable');
                }
            },

            // Switch to Desktop Version
            exit: function() {
                // minicart
                $('.action.showcart').removeClass('is-disabled');

                (function() {

                    var productInfoMain = $('.product.info.main'),
                        productInfoAdditional = $("#product-info-additional");

                    if(productInfoAdditional.length) {
                        productInfoAdditional.addClass("hidden");
                        productInfoMain.removeClass("responsive");
                    }

                })();

                var galleryElement = $('[data-role=media-gallery]');
                if (galleryElement.length && galleryElement.data('zoom')) {
                    galleryElement.zoom('enable');
                }
            }
        });
    });
})(window.jQuery);
