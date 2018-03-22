/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_ConfigurableProduct/js/variations/steps/bulk',
    'jquery',
    'ko',
    'underscore'
], function (Bulk, $, ko, _) {
    'use strict';

    return Bulk.extend({
        defaults: {
            quantityModuleName: '',
            quantity_per_source: '',
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
            var self = this;
            var sections;
            this._super();

            sections = this.sections();
            sections.quantity_per_source = {
                label: 'quantity per source',
                    type: ko.observable('none'),
                    value: ko.observable(),
                    attribute: ko.observable()
            };
            this.sections(sections);

            /**
             * Make options sections.
             */
            this.makeOptionSections = function () {
                this.images = new self.makeImages(null);
                this.price = self.price;
                this.quantity = self.quantity;
                this.quantity_per_source = self.quantity_per_source;
            };

            this.initAttributeListener();

            return this;
        },

        /**
         * Inits listeners for attribute change.
         */
        initAttributeListener: function () {
            var quantity_per_source = this.sections().quantity_per_source;

            quantity_per_source.attribute.subscribe(function (data) {
                this.attribute(data);
            }.bind(this));

            quantity_per_source.type.subscribe(function (data) {
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
                    item.sections().quantity_per_source = data[item.label];
                });
            } else if (this.type() === 'single') {
                data = module.dynamicRowsCollection[module.dynamicRowsName];
                this.sections().quantity_per_source.value(data);
            }
        },

        /** @inheritdoc */
        validate: function () {
            var valid = true,
                quantity_per_source = this.quantityResolver();

            this._super();

            if (this.type() && this.type() !== 'none') {
                quantity_per_source.valid = true;

                quantity_per_source.elems().forEach(function (item) {
                    quantity_per_source.validate.call(quantity_per_source, item);
                    valid = valid && item.elems()[1].elems().length;
                });

                if (!quantity_per_source.valid || !valid) {
                    throw new Error($.mage.__('Please fill-in correct values.'));
                }
            }
        }
    });
});
