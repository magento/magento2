/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment/renderer-list',
    'Magento_Ui/js/core/renderer/layout'

], function (_, ko, utils, Component, paymentMethods, rendererList, layout) {
    'use strict';

    var cm = null;


    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment-methods/list',
            visible: paymentMethods().length > 0
        },
        initialize: function () {
            this._super()
                .initChildren();

            paymentMethods.subscribe(
                function (changes) {
                    var self = this;
                    changes.forEach(function(change) {
                        if (change.status === 'added') {
                            console.log(('added ' + change.value.code));
                            self.createRenderer(change.value);
                        } else if (change.status === 'deleted') {
                            console.log(('deleted ' + change.value.code));
                            self.removeRenderer(change.value);
                        }
                    });
                },
                this,
                'arrayChange'
            );
            return this;
        },

        initChildren: function () {
            var self = this;
            paymentMethods().forEach(function (item ) {
                self.createRenderer(item);
            });
            return this;
        },

        createRenderer: function(item) {
            var renderer = this.getRendererByType(item.code);
            if (renderer) {
                var templateData = {
                    parentName: this.name,
                    name: item.method
                };
                var rendererTemplate = {
                    parent: '${ $.$data.parentName }',
                    name: '${ $.$data.name }',
                    component: renderer.component
                };
                var rendererComponent = utils.template(rendererTemplate, templateData);
                utils.extend(rendererComponent, {item: item});
                cm = rendererComponent;
                layout([rendererComponent]);
            } else {
                console.log('There is no registered render for Payment Method: ' + item.code);
            }
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

        removeRenderer: function (paymentMethod) {
            this.removeChild(cm);
        }
    });
});
