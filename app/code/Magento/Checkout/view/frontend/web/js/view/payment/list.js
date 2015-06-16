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
    'uiRegistry',
    'Magento_Checkout/js/model/payment/renderer-list'

], function (_, ko, utils, Component, paymentMethods, layout, registry, rendererList) {
    'use strict';

    var createRenderer = function(item) {
        var renderer = getRendererByType(item.method);
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
            layout([rendererComponent]);
        } else {
            console.log('There is no registered render for Payment Method: ' + item.method);
        }
    };

    var getRendererByType = function(code) {
        var output = null;
        rendererList().forEach(function(item) { //todo refactor this code
            if (item.type == code) {
                output = item;
            }
        });
        return output;
    };

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
                            console.log(('added ' + change.value.method));
                            createRenderer(change.value);
                        } else if (change.status === 'deleted') {
                            console.log(('deleted ' + change.value.method));
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
            paymentMethods().forEach(function (item ) {
                createRenderer(item);
            });
            return this;
        },

        add: function() {
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
            paymentMethods([method]);
        },

        remove: function() {
            paymentMethods([]);
        },

        removeRenderer: function (paymentMethod) {
            console.info('remove renderer');
            registry.remove([paymentMethod.method]);
        }
    });
});
