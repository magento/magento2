/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['underscore'], function (_) {
    'use strict';

    return function ($target, $owner, data) {
        var isUnlock = true;

        _.every(data.argument, function (name) {
            if (data.solutionsElements[name] &&
                data.solutionsElements[name].find(data.enableButton).val() === '1'
            ) {
                isUnlock = false;

                return isUnlock;
            }

            return isUnlock;
        }, this);

        if (isUnlock) {
            $target.find(data.buttonConfiguration).removeClass('disabled')
                .removeAttr('disabled');
        }
    };
});
