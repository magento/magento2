/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['underscore'], function (_) {
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
            $target.find(data.enableBml).prop('disabled', true);
            $target.find(data.enableBml + ' option[value="1"]').prop('selected', true);
            $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').addClass('enabled');
        } else {
            $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').removeClass('enabled');
            $target.find(data.enableBml + ' option[value="0"]').prop('selected', true);
            $target.find(data.enableBml).prop('disabled', true);
        }
    };
});
