/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Magento_Customer/js/model/address-list'
], function (_, ko, utils, Component, layout, addressList) {
    'use strict';
    var defaultRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default'
    };

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/list',
            visible: addressList().length > 0,
            rendererTemplates: []
        },

        initialize: function () {
            this._super()
                .initChildren();

            addressList.subscribe(
                function(changes) {
                    var self = this;
                    changes.forEach(function(change) {
                        if (change.status === 'added') {
                           self.createRendererComponent(change.value, change.index);
                        }
                    });
                },
                this,
                'arrayChange'
            );
            return this;
        },

        initConfig: function () {
            this._super();
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];
            return this;
        },

        initChildren: function () {
            _.each(addressList(), this.createRendererComponent, this);
            return this;
        },

        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param index
         */
        createRendererComponent: function (address, index) {
            if (index in this.rendererComponents) {
                this.rendererComponents[index].address(address);
            } else {
                // rendererTemplates are provided via layout
                var rendererTemplate = (address.getType() != undefined && this.rendererTemplates[address.getType()] != undefined)
                    ? utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.getType()])
                    : defaultRendererTemplate;
                var templateData = {
                    parentName: this.name,
                    name: index
                };
                var rendererComponent = utils.template(rendererTemplate, templateData);
                utils.extend(rendererComponent, {address: ko.observable(address)});
                layout([rendererComponent]);
                this.rendererComponents[index] = rendererComponent;
            }
        }
    });
});
