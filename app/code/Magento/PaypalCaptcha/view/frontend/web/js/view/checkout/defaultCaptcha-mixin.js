/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_PaypalCaptcha/js/model/skipRefreshCaptcha'
], function (skipRefreshCaptcha) {
    'use strict';

    var defaultCaptchaMixin = {
        /**
         * @override
         */
        refresh: function () {
            if (!skipRefreshCaptcha.skip()) {
                this._super();
            } else {
                skipRefreshCaptcha.skip(false);
            }
        }
    };

    return function (defaultCaptcha) {
        return defaultCaptcha.extend(defaultCaptchaMixin);
    };
});
