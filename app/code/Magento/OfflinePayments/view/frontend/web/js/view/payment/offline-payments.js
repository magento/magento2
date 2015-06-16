/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/provider',
        'Magento_OfflinePayments/js/view/payment/method-renderer/checkmo-method'
    ],
    function (
        Component,
        paymentList,
        checkmoRenderer
    ) {
        'use strict';
        //Register gift registry rate processor
        paymentList.registerRenderer(checkmoRenderer);

        /** Add view logic here if needed */
        return Component.extend({});
    }
);