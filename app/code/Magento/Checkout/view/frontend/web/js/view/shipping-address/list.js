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
    // TODO inject address renderers and get addresses. Remove this hardcoded template.
    var rendererTemplates = {
        'gift-registry': {
            component: 'Magento_GiftRegistry/js/view/shipping-address/address-renderer/gift-registry'
        }
    };

    var addresses = addressList.getAddresses();
    addresses.forEach(function(address) {
        address.countryName = window.checkoutConfig.countryData[address.countryId].name;
    });

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/list',
            visible: window.checkoutConfig.customerAddressCount
        },

        initialize: function () {
            this._super()
                .initChildren();
            return this;
        },

        initChildren: function () {
            _.each(addresses, this.createRendererComponent, this);

            return this;
        },

        createRendererComponent: function (address, addressIndex) {
            address.type = 'gift-registry';
            var rendererTemplate = (address.type != undefined)
                ? utils.extend(defaultRendererTemplate, rendererTemplates[address.type])
                : defaultRendererTemplate;
            var templateData = {
                parentName: this.name,
                name: addressIndex
            };
            var rendererComponent = utils.template(rendererTemplate, templateData);

            utils.extend(
                rendererComponent,
                {
                    address: address
                }
            );

            layout([rendererComponent]);
        },

        selectedAddress: ko.computed(function(){
            if (!quote.getShippingAddress()()) {
                quote.setShippingAddress(addressList.getDefaultShipping());
            }
            return quote.getShippingAddress()();
        })
    });
});
