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
            quantityPerSource: '',
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
            var sections;

            this._super();

            sections = this.sections();
            sections.quantityPerSource = {
                label: 'Quantity Per Source',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            };
            this.sections(sections);

            /**
             * Make options sections.
             */
            this.makeOptionSections = function () {
                return {
                    images: new this.makeImages(null),
                    price: this.price,
                    quantityPerSource: this.quantityPerSource
                };
            }.bind(this);

            this.initAttributeListener();

            return this;
        },

        /**
         * Inits listeners for attribute change.
         */
        initAttributeListener: function () {
            var quantityPerSource = this.sections().quantityPerSource;

            quantityPerSource.attribute.subscribe(function (data) {
                this.attribute(data);
            }.bind(this));

            quantityPerSource.type.subscribe(function (data) {
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
                    item.sections().quantityPerSource = data[item.label];
                });
            } else if (this.type() === 'single') {
                data = module.dynamicRowsCollection[module.dynamicRowsName];
                this.sections().quantityPerSource.value(data);
            }
        },

        /** @inheritdoc */
        validate: function () {
            var valid = true,
                quantityPerSource = this.quantityResolver();

            this._super();

            if (this.type() && this.type() !== 'none') {
                quantityPerSource.valid = true;

                quantityPerSource.elems().forEach(function (item) {
                    quantityPerSource.validate.call(quantityPerSource, item);
                    valid = valid && item.elems()[1].elems().length;
                });

                if (!quantityPerSource.valid || !valid) {
                    throw new Error($.mage.__('Please fill-in correct values.'));
                }
            }
        }
    });
});
