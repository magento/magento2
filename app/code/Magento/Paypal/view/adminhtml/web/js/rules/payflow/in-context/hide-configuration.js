/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function () {
    'use strict';

    define([], function () {
        return function ($target, $owner, data) {
            $target.find('tr[id$="_settings_express_checkout"], tr[id$="_payflow_express_checkout"], tr[id$="_settings_payflow_link_express_checkout"]').hide();
        };
    });
})();
