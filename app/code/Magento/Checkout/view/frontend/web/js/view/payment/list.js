/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Ui/js/core/renderer/layout',
    'Magento_Checkout/js/model/payment/payment-list'
], function (_, ko, utils, Component, layout, paymentMethods) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment-methods/list',
            visible: paymentMethods().length > 0,
        },
        initialize: function () {
            this._super()
                .initChildren();

            paymentMethods.subscribe(
                function (changes) {
                    var self = this;
                    changes.forEach(function(change) {
                        if (change.status === 'added') {
                            self.addRenderer(change.value, change.index);
                        } else if (change.status === 'deleted') {
                            self.removeRenderer(change.value, change.index);
                        }
                    });
                },
                this,
                'arrayChange'
            );

            return this;
        },

        initProperties: function () {
            this._super();
            this.processors = {};

            return this;
        },

        initChildren: function () {
            _.each(paymentMethods(), this.registerRenderer, this);
            return this;
        },

        registerRenderer: function(type, renderer) {
            this.processors[type] = renderer;
        },

        addRenderer: function (paymentMethod) {
            return;
            //var code = paymentMethod.code;
            //renderer[code].render;
        },
        removeRenderer: function (paymentMethod) {
            return;
        }
    });
});
