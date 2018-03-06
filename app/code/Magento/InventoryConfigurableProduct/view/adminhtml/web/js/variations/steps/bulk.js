/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_ConfigurableProduct/js/variations/steps/bulk',
    'jquery',
    'underscore'
], function (Bulk, $, _) {
    'use strict';

    return Bulk.extend({
        defaults: {
            quantityModuleName: '',
            exports: {
                attribute: '${$.provider}:data.inventoryAttribute',
                type: '${$.provider}:data.inventoryType'
            },
            modules: {
                quantityResolver: '${$.quantityResolver}'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.initAttributeListener();

            return this;
        },

        /**
         * Inits listeners for attribute change.
         */
        initAttributeListener: function () {
            var quantity = this.sections().quantity;

            quantity.attribute.subscribe(function (data) {
                this.attribute(data);
            }.bind(this));

            quantity.type.subscribe(function (data) {
                this.type(data);
            }.bind(this));
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'attribute',
                    'type'
                ]);

            return this;
        },

        /** @inheritdoc */
        force: function (wizard) {
            if (this.type() === 'each' && this.attribute() || this.type() === 'single') {
                this.prepareDynamicRowsData();
            }

            this._super(wizard);
        },

        /**
         * Prepares dynamic rows data for the next step
         */
        prepareDynamicRowsData: function () {
            var data,
                module = this.quantityResolver();

            if (this.type() === 'each') {
                data = module.dynamicRowsCollection[this.attribute().code];

                _.each(this.attribute().chosen, function (item) {
                    item.sections().quantity = data[item.label];
                });
            } else if (this.type() === 'single') {
                data = module.dynamicRowsCollection[module.dynamicRowsName];
                this.sections().quantity.value(data);
            }
        },

        /** @inheritdoc */
        validate: function () {
            var valid = true,
                quantity = this.quantityResolver();

            this._super();

            if (this.type() && this.type() !== 'none') {
                quantity.valid = true;

                quantity.elems().forEach(function (item) {
                    quantity.validate.call(quantity, item);
                    valid = valid && item.elems()[1].elems().length;
                });

                if (!quantity.valid || !valid) {
                    throw new Error($.mage.__('Please fill-in correct values.'));
                }
            }
        }
    });
});
