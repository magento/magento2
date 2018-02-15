/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_ConfigurableProduct/js/variations/steps/bulk',
    'jquery'
], function (Bulk, $) {
    'use strict';

    return Bulk.extend({
        defaults: {
            quantityModuleName: '',
            exports: {
                attribute: '${$.provider}:data.inventoryAttribute',
                type: '${$.provider}:data.inventoryType'
            },
            modules: {
                quantityEachResolver: '${$.quantityEachResolver}',
                quantitySingleResolver : '${$.quantitySingleResolver}'
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
            var result = [],
                data,
                module;

            if (this.type() === 'each') {
                module = this.quantityEachResolver();
                data = this.quantityEachResolver().dynamicRowsCollection[this.attribute().code];

                _.each(this.attribute().chosen, function (item) {
                    item.sections()['quantity'] = data[item.label]
                });
            } else if (this.type() === 'single') {
                module = this.quantitySingleResolver();

                data = module.dynamicRowsCollection[module.dynamicRowsName];
                this.sections().quantity.value(data);
            }
        },

        /**
         * @returns {Object|Null} quantity module
         */
        getQuantityModule: function () {
            switch (this.type()) {
                case 'each':
                    return this.quantityEachResolver();
                case 'single':
                    return this.quantitySingleResolver();
            }

            return null;
        },

        /** @inheritdoc */
        validate: function () {
            var valid = true,
                quantity = this.getQuantityModule();

            this._super();

            if (this.type() && this.type() !== 'none') {
                quantity.valid = true;

                quantity.elems().forEach(function (item) {
                    quantity.validate.call(quantity, item);
                    valid = valid && item.elems()[1].elems().length
                }.bind(this));

                if (!quantity.valid || !valid) {
                    throw new Error($.mage.__('Please fill-in correct values.'));
                }
            }
        }
    });
});
