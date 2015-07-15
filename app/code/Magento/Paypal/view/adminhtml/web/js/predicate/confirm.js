/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    "use strict";
    return function (solution, message, argument) {
        var isConfirm = false;

        _.every(argument, function (name) {
            if (solution.solutionsElements[name]
                && solution.solutionsElements[name].find(solution.enableButton).val() == 1
            ) {
                isConfirm = true;
                return !isConfirm;
            }
            return !isConfirm;
        }, this);

        if (isConfirm) {
            return confirm(message);
        } else {
            return true;
        }
    };
});
