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
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default'
    };

    var observableAddresses = addressList.getAddresses();

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/list',
            visible: (observableAddresses().length > 0),
            rendererTemplates: []
        },

        initialize: function () {
            this._super()
                .initChildren();
            observableAddresses.subscribe(
                function(addresses) {
                    var addressIndex = addresses.length - 1;
                    if (addresses.length == this.addressCount) {
                        // Update last added address (customer can update only one address)
                        this.lastAddedAddress(addresses[addressIndex]);
                    } else if (addresses.length > this.addressCount) {
                        var address = addresses[addressIndex];
                        // Add a new tile for newly added address
                        this.createRendererComponent(address, addressIndex);
                        this.addressCount++;
                        // New address must be selected as shipping address
                        if (address.hasOwnProperty('customerAddressId') && address.customerAddressId == undefined) {
                            this.selectAddressTile(addressIndex);
                        }
                    }
                },
                this
            );

            return this;
        },

        initProperties: function () {
            this._super();

            this.lastAddedAddress = ko.observable({});
            this.addresses = observableAddresses();
            // number of addresses already shown in the list
            this.addressCount = this.addresses.length;
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];

            return this;
        },

        initChildren: function () {
            _.each(this.addresses, this.createRendererComponent, this);

            return this;
        },

        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param addressIndex
         */
        createRendererComponent: function (address, addressIndex) {
            // rendererTemplates are provided via layout
            var rendererTemplate = (address.type != undefined && this.rendererTemplates[address.type] != undefined)
                ? utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.type])
                : defaultRendererTemplate;
            var templateData = {
                parentName: this.name,
                name: addressIndex
            };
            var rendererComponent = utils.template(rendererTemplate, templateData);
            // remember last added address
            this.lastAddedAddress = ko.observable(address);
            utils.extend(
                rendererComponent,
                {
                    address: this.lastAddedAddress,
                    isSelected: ko.observable(!!address.isDefaultShipping)
                }
            );

            layout([rendererComponent]);
            this.rendererComponents[addressIndex] = rendererComponent;
        },

        /**
         * Select corresponding address tile.
         * This method must be called by address renderer component in order to select it and unselect other tiles.
         *
         * @param addressIndex
         */
        selectAddressTile: function(addressIndex) {
            this.rendererComponents.forEach(function(rendererComponent, rendererIndex) {
                rendererComponent.isSelected(rendererIndex == addressIndex);
            });
        }
    });
});
