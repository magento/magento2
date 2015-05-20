/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        "mage/translate",
        'Magento_Checkout/js/view/payment/method-info'
    ],
    function ($, $t, methodInfo) {
        return methodInfo.extend({
            defaults: {
                purchaseOrderNumber: ''
            },
            initObservable: function () {
                this._super()
                    .observe('purchaseOrderNumber');
                return this;
            },
            getData: function() {
                return {'po_number': this.purchaseOrderNumber()};
            },
            getInfo: function() {
                return [
                    {'name': 'Purchase Order Number', value: this.purchaseOrderNumber()}
                ];
            }
        });
    }
);
