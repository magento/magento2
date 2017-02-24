/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list',
    'uiLayout',
    'uiRegistry'
], function (_, Component, rendererList, layout, registry) {
    'use strict';

    var vaultGroupName = 'vaultGroup';

    layout([{
        name: vaultGroupName,
        component: 'Magento_Checkout/js/model/payment/method-group',
        alias: 'vault',
        sortOrder: 10
    }]);

    registry.get(vaultGroupName, function (vaultGroup) {
        _.each(window.checkoutConfig.payment.vault, function (config, index) {
            rendererList.push(
                {
                    type: index,
                    config: config.config,
                    component: config.component,
                    group: vaultGroup,

                    /**
                     * Custom payment method types comparator
                     * @param {String} typeA
                     * @param {String} typeB
                     * @return {Boolean}
                     */
                    typeComparatorCallback: function (typeA, typeB) {
                        // vault token items have the same name as vault payment without index
                        return typeA.substring(0, typeA.lastIndexOf('_')) === typeB;
                    }
                }
            );
        });
    });

    /**
     * Add view logic here if needed
     */
    return Component.extend({});
});
