/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'mage/storage',
        'Magento_Ui/js/model/errorlist'
    ],
    function(storage, errorList) {
        "use strict";
        return function(captcha, deferred) {
            return storage.post(
                'captcha/validate',
                JSON.stringify({
                    'formId': captcha.formId,
                    'captchaText': captcha.getCaptchaValue()()
                })
            ).done(
                function (response) {
                    if (response.error > 0) {
                        errorList.add(response);
                        deferred.reject();
                        captcha.refresh();
                        captcha.setCaptchaValue(null);
                    } else {
                        deferred.resolve();
                    }
                }
            ).fail(
                function () {
                    captcha.setCaptchaValue(null);
                    deferred.reject();
                }
            );
        };
    }
);
