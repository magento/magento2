/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Ui/js/core/renderer/layout',
    'Magento_Checkout/js/model/addresslist',
    'Magento_Checkout/js/model/quote'
], function (_, ko, utils, Component, layout, addressList, quote) {
    'use strict';
    var defaultRendererTemplate = {
        parent: '<%= $data.parentName %>',
        name: '<%= $data.name %>',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default'
    };
    var observableAddresses = addressList.getAddresses();
    var addresses = observableAddresses();
    var addressCount = addresses.length;
    var lastAddedAddress = ko.observable({});

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/list',
            visible: window.checkoutConfig.customerAddressCount
        },

        initialize: function () {
            this._super()
                .initChildren();
            observableAddresses.subscribe(
                function(addresses) {
                    var addressIndex = addresses.length - 1;
                    if (addresses.length == addressCount) {
                        // Update last added address (customer can update only one address)
                        lastAddedAddress(addresses[addressIndex]);
                    } else if (addresses.length > addressCount) {
                        // Add a new tile for newly added address
                        this.createRendererComponent(addresses[addressIndex], addressIndex);
                        addressCount++;
                    }
                },
                this
            );

            return this;
        },

        initChildren: function () {
            this.rendererComponents = [];
            _.each(addresses, this.createRendererComponent, this);

            return this;
        },

        createRendererComponent: function (address, addressIndex) {
            // rendererTemplates are provided via layout
            var rendererTemplate = (address.type != undefined && this.rendererTemplates[address.type] != undefined)
                ? utils.extend(defaultRendererTemplate, this.rendererTemplates[address.type])
                : defaultRendererTemplate;
            var templateData = {
                parentName: this.name,
                name: addressIndex
            };
            var rendererComponent = utils.template(rendererTemplate, templateData);
            // remember last added address
            lastAddedAddress = ko.observable(address);
            utils.extend(
                rendererComponent,
                {
                    address: lastAddedAddress,
                    isSelected: ko.observable(!!address.isDefaultShipping)
                }
            );

            layout([rendererComponent]);
            this.rendererComponents[addressIndex] = rendererComponent;
        },

        selectAddressTile: function(addressIndex) {
            this.rendererComponents.forEach(function(rendererComponent, rendererIndex) {
                rendererComponent.isSelected(rendererIndex == addressIndex);
            });
        },

        selectedAddress: ko.computed(function(){
            if (!quote.getShippingAddress()()) {
                quote.setShippingAddress(addressList.getDefaultShipping());
            }
            return quote.getShippingAddress()();
        })
    });
});
