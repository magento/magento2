/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Braintree/js/button/builder',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/apply/main'
], function ($, builder, domObserver, mage) {
    'use strict';

    return function (config) {
        var $container = $(config.containerId);

        domObserver.off(config.containerId);
        domObserver.get(config.containerId, function () {
            mage.apply();
        });

        if ($container.data('is_rendered')) {
            return;
        }
        $container.data('is_rendered', true);

        builder.setClientToken(config.clientToken)
            .setOptions(config.options)
            .setName('paypal')
            .setContainer(config.containerId)
            .setPayment(config.paymentId)
            .setDetails(config.detailsId)
            .setFormAction(config.formAction)
            .build();
    };
});
