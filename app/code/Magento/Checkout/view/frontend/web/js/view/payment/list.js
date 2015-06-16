/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Checkout/js/model/payment/payment-list',
    'Magento_Ui/js/core/renderer/layout',
    'Magento_Checkout/js/model/payment/renderer-list'

], function (_, ko, utils, Component, paymentMethods, layout, rendererList) {
    'use strict';

    //debugger;
    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment-methods/list',
            visible: paymentMethods().length > 0
        },
        initialize: function () {
            this._super();

            paymentMethods.subscribe(
                function (changes) {
                    var self = this;
                    changes.forEach(function(change) {
                        if (change.status === 'added') {
                            self.addRenderer(change);
                        } else if (change.status === 'deleted') {
                            self.removeRenderer(change.value, change.index);
                        }
                    });
                },
                this,
                'arrayChange'
            );

            this.getPaymentMethods();

            return this;
        },

        addRenderer: function (paymentMethod) {
            console.info('add renderer ' + paymentMethod.method);
        },
        removeRenderer: function (paymentMethod) {
            console.info('remove renderer');
        },

        getRendererByType: function(code) {
            var output = null;
            rendererList().forEach(function(item) { //todo refactor this code
                if (item.type == code) {
                    output = item;
                }
            });
            return output;
        },

        getPaymentMethods: function() {
            var output = [];
            var self = this;
            paymentMethods().forEach(function(item){
                var renderer = self.getRendererByType(item.method);
                var rendererTemplate = {
                    parent: '${ $.$data.parentName }',
                    name: '${ $.$data.name }',
                    component: ''
                };
                if (renderer) {
                    rendererTemplate.component = renderer.component;
                    var templateData = {
                        parentName: self.name,
                        name: 1
                    };
                    var rendererComponent = utils.template(rendererTemplate, templateData);
                    utils.extend(rendererComponent, {item: item});
                    layout([rendererComponent]);
                    output.push(rendererComponent);
                } else {
                    console.info('no registered render for type ' + item.method);
                }
            });
            return output;
        }
    });
});
