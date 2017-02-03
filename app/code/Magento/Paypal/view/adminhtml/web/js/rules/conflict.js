/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
(function () {
    'use strict';

    var executed = false;

    define([
        'Magento_Ui/js/modal/alert',
        'underscore'
    ], function (alert, _) {

        return function ($target, $owner, data) {

            var isDisabled = true,
                newLine = String.fromCharCode(10, 13);

            if ($owner.find(data.enableButton).val() === '1') {
                _.every(data.argument, function (name) {
                    if (data.solutionsElements[name] &&
                        data.solutionsElements[name].find(data.enableButton).val() === '1'
                    ) {
                        isDisabled = false;

                        return isDisabled;
                    }

                    return isDisabled;
                }, this);

                if (!isDisabled && !executed) {
                    executed = true;
                    alert({
                        content: 'The following error(s) occurred:' +
                        newLine +
                        'Some PayPal solutions conflict.' +
                        newLine +
                        'Please re-enable the previously enabled payment solutions.'
                    });
                }
            }
        };
    });
})();
