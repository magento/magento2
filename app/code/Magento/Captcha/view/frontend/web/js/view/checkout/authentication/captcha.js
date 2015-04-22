/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Customer/js/model/customer',
        'mage/storage'
    ],
    function ($, ko, Component, customer, storage) {
        "use strict";
        var config = window.checkoutConfig;
        var imageSrc = ko.observable(config.captchaImageSrc);
        var refreshCaptcha = function() {
            storage.post(
                config.captchaRefreshUrl,
                JSON.stringify({'formId': config.captchaFormId})
            ).done(
                function (response) {
                    if (response.imgSrc) {
                        imageSrc(response.imgSrc);
                    }
                }
            );
        };

        customer.getFailedLoginAttempts().subscribe(function() {
            refreshCaptcha();
        });

        return Component.extend({
            defaults: {
                template: 'Magento_Captcha/checkout/authentication/captcha'
            },
            isRequired: config.captchaIsRequired,
            formId: config.captchaFormId,
            isCaseSensitive: config.captchaIsCaseSensitive,
            imageHeight: config.captchaImageHeight,
            imageSrc: imageSrc,
            refresh: refreshCaptcha
        });
    }
);
