/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        if ($target.find(data.enableInContext).val() === '1') {
            $target.find('tr[id$="_settings_express_checkout"], tr[id$="_payflow_express_checkout"], tr[id$="_settings_payflow_link_express_checkout"]').hide();
        }
    };
});
