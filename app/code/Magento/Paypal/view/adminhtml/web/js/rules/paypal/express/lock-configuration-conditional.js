/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/paypal/express/lock-configuration',
    'underscore'
], function (lockConfiguration, _) {
    'use strict';

    return function ($target, $owner, data) {
        var isDisabled = true;

        _.every(data.argument, function (name) {
            if (data.solutionsElements[name] &&
                data.solutionsElements[name].find(data.enableButton).val() === '1'
            ) {
                isDisabled = false;

                return isDisabled;
            }

            return isDisabled;
        }, this);

        if (!isDisabled) {
            lockConfiguration($target, $owner, data);
        }
    };
});
