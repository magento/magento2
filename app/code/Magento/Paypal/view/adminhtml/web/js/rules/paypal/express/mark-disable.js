/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/simple/disable',
    'underscore'
], function (disable, _) {
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

        if (isDisabled) {
            disable($target, $owner, data);
        }
    };
});
