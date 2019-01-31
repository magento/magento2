/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'underscore'
], function (Select, uiRegistry, _) {
    'use strict';

    return Select.extend({
        defaults: {
            prices: {}
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super()
                .prepareForm();
        },

        /**
         * {@inheritdoc}
         */
        setInitialValue: function () {
            this.initialValue = this.getInitialValue();

            if (this.value.peek() !== this.initialValue) {
                this.value(this.initialValue);
            }

            this.isUseDefault(this.disabled());

            return this;
        },

        /**
         * {@inheritdoc}
         */
        prepareForm: function () {
            var elements = this.getElementsByPrices(),
                prices = this.prices,
                currencyType = _.keys(prices)[0],
                select = this;

            uiRegistry.get(elements, function () {
                _.each(arguments, function (currentValue) {
                    if (parseFloat(currentValue.value()) > 0) {
                        _.each(prices, function (priceValue, priceKey) {
                            if (priceValue === currentValue.name) {
                                currencyType = priceKey;
                            }
                        });
                    }
                });
                select.value(currencyType);
                select.on('value', select.onUpdate.bind(select));
                select.onUpdate();
            });
        },

        /**
         * @returns {Array}
         */
        getElementsByPrices: function () {
            var elements = [];

            _.each(this.prices, function (currentValue) {
                elements.push(currentValue);
            });

            return elements;
        },

        /**
         * Callback that fires when 'value' property is updated
         */
        onUpdate: function () {
            var value = this.value(),
                prices = this.prices,
                select = this,
                parentDataScopeArr = this.dataScope.split('.'),
                parentDataScope,
                elements = this.getElementsByPrices();

            parentDataScopeArr.pop();
            parentDataScope = parentDataScopeArr.join('.');

            uiRegistry.get(elements, function () {
                var sourceData = select.source.get(parentDataScope);

                _.each(arguments, function (currentElement) {
                    var index;

                    _.each(prices, function (priceValue, priceKey) {
                        if (priceValue === currentElement.name) {
                            index = priceKey;
                        }
                    });

                    if (value === index) {
                        currentElement.visible(true);
                        sourceData[currentElement.index] = currentElement.value();
                    } else {
                        currentElement.value('');
                        currentElement.visible(false);
                        delete sourceData[currentElement.index];
                    }
                });
                select.source.set(parentDataScope, sourceData);
            });
        }
    });
});
