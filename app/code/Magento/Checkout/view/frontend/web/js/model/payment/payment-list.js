/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Checkout/js/model/payment-service'
    ],
    function (ko, defaultProvider) {
        'use strict';

        //todo: MAGETWO-38726
        //var data = ko.observableArray(defaultProvider.paymentMethods());
        //defaultProvider.paymentMethods.subscribe(function () {
        //        data(defaultProvider.paymentMethods())
        //    }
        //);

        var method = {
            "method": 'checkmo',
            "po_number": null,
            "cc_owner": null,
            "cc_number": null,
            "cc_type": null,
            "cc_exp_year": null,
            "cc_exp_month": null,
            "additional_data": null
        };
        var data = ko.observableArray([method]);
        return data;
    }
);
