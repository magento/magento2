/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function() {
    var executed = false;
    define([], function () {
        "use strict";
        return function ($target, $owner, data) {
            if ($owner.find(data.enableButton).val() == 1) {
                var isDisabled = true;

                _.every(data.argument, function (name) {
                    if (data.solutionsElements[name]
                        && data.solutionsElements[name].find(data.enableButton).val() == 1
                    ) {
                        isDisabled = false;
                        return isDisabled;
                    }
                    return isDisabled;
                }, this);

                if (!isDisabled && !executed) {
                    executed = true;
                    alert(
                        "The following error(s) occured:\n\r"
                        + "Some PayPal solutions conflict.\n\r"
                        + "Please re-enable the previously enabled payment solutions."
                    );
                }
            }
        };
    });
})();
