/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/actions',
                displayArea: 'actions'
            },
            getActiveView: function() {
                var view = this.getViewByCode(quote.paymentMethod());
                return view ? view : this.getDefaultView();
            },
            getViewByCode: function(code) {
                return _.find(this.elems(), function(elem) {
                    return elem.index == code && !elem.isDefault;
                });
            },
            getDefaultView: function() {
                return _.find(this.elems(), function(elem) {
                    return elem.isDefault;
                });
            }
        });
    }
);
