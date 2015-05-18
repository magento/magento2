/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/generic',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, generic, quote) {
        return generic.extend({
            defaults: {
                isChecked: false
            },
            isAvailable: function() {
                return false;
            },
            isOn: function() {
                return false;
            },
            getBalance: function() {
                return 0;
            },
            initObservable: function () {
                this._super()
                    .observe('isChecked');

                var self = this;
                this.isChecked.subscribe(
                    function(isChecked) {
                        if (isChecked) {
                            quote.setCollectedTotals(self.getCode(), -parseFloat(self.getBalance()));
                        } else {
                            quote.setCollectedTotals(self.getCode(), 0);
                        }
                    }
                );
                this.isChecked(this.isOn());

                return this;
            },
            isActive: function() {
                return this.isChecked();
            }
        });
    }
);
