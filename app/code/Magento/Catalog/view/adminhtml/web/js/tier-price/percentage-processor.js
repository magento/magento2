/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_Catalog/js/utils/percentage-price-calculator'
], function (Element, _, $, percentagePriceCalculator) {
    'use strict';

    return Element.extend({
        defaults: {
            priceElem: '${ $.parentName }.price',
            selector: 'input',
            imports: {
                priceValue: '${ $.priceElem }:priceValue'
            },
            exports: {
                calculatedVal: '${ $.priceElem }:value'
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super();

            _.bindAll(this, 'initPriceListener', 'onInput');

            $.async({
                component: this.priceElem,
                selector: this.selector
            }, this.initPriceListener);

            return this;
        },

        /**
         * {@inheritdoc}
         */
        initObservable: function () {
            return this._super()
                .observe(['visible']);
        },

        /**
         * Handles keyup event on price input.
         *
         * {@param} HTMLElement elem
         */
        initPriceListener: function (elem) {
            $(elem).on('keyup.priceCalc', this.onInput);
        },

        /**
         * Delegates calculation of the price input value to percentagePriceCalculator.
         *
         * {@param} object event
         */
        onInput: function (event) {
            var value = event.currentTarget.value;

            if (value.slice(-1) === '%') {
                value = percentagePriceCalculator(this.priceValue, value);
                this.set('calculatedVal', value);
            }
        }
    });
});
