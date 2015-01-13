/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global Handlebars*/
define([
    "jquery",
    "Magento_Catalog/js/price-utils",
    "underscore",
    "handlebars",
    "jquery/ui"
], function ($, utils, _) {
    "use strict";

    var globalOptions = {
        productId: null,
        priceConfig: null,
        prices: {},
        priceTemplate: '<span class="price">{{formatted}}</span>'
    };
    var hbs = Handlebars.compile;


    $.widget('mage.priceBox', {
        options: globalOptions,
        _init: initPriceBox,
        _create: createPriceBox,
        _setOptions: setOptions,
        updatePrice: updatePrices,
        reloadPrice: reDrawPrices,
        setDefault: setDefaultPrices,

        cache: {}
    });

    return $.mage.priceBox;

    /**
     * Widget initialisation.
     * Every time when option changed prices also can be changed. So
     * changed options.prices -> changed cached prices -> recalculation -> redraw price box
     */
    function initPriceBox() {
        /*jshint validthis: true */
        var box = this.element;
        box.trigger('updatePrice');
        this.cache.displayPrices = utils.deepClone(this.options.prices);
    }

    /**
     * Widget creating.
     */
    function createPriceBox() {
        /*jshint validthis: true */
        var box = this.element;

        setDefaultsFromPriceConfig.call(this);
        setDefaultsFromDataSet.call(this);

        box.on('reloadPrice', reDrawPrices.bind(this));
        box.on('updatePrice', onUpdatePrice.bind(this));
    }

    /**
     * Call on event updatePrice. Proxy to updatePrice method.
     * @param {Event} event
     * @param {Object} prices
     * @param {Boolean} isReplace
     * @return {Function}
     */
    function onUpdatePrice(event, prices, isReplace) {
        /*jshint validthis: true */
        return updatePrices.call(this, prices, isReplace);
    }

    /**
     * Updates price via new (or additional values).
     * It expects object like this:
     * -----
     *   "option-hash":
     *      "price-code":
     *         "amount": 999.99999,
     *         ...
     * -----
     * Empty option-hash object or empty price-code object treats as zero amount.
     * @param {Object} newPrices
     */
    function updatePrices(newPrices) {
        /*jshint validthis: true */
        var prices = this.cache.displayPrices;
        var additionalPrice = {};
        var keys = [];

        this.cache.additionalPriceObject = this.cache.additionalPriceObject || {};
        if (newPrices) {
            $.extend(this.cache.additionalPriceObject, newPrices);
        }
        if (!_.isEmpty(additionalPrice)) {
            keys = _.keys(additionalPrice);
        } else if (!_.isEmpty(prices)) {
            keys = _.keys(prices);
        }

        _.each(this.cache.additionalPriceObject, function (additional) {
            if (additional && !_.isEmpty(additional)) {
                keys = _.keys(additional);
            }
            _.each(keys, function (priceCode) {
                var priceValue = additional[priceCode] || {};
                priceValue.amount = +priceValue.amount || 0;
                priceValue.adjustments = priceValue.adjustments || {};

                additionalPrice[priceCode] = additionalPrice[priceCode] || {'amount': 0, 'adjustments': {}};
                additionalPrice[priceCode].amount = 0 + (additionalPrice[priceCode].amount || 0) + priceValue.amount;
                _.each(priceValue.adjustments, function (adValue, adCode) {
                    additionalPrice[priceCode].adjustments[adCode] = 0 + (additionalPrice[priceCode].adjustments[adCode] || 0) + adValue;
                });
            });
        });

        if (_.isEmpty(additionalPrice)) {
            this.cache.displayPrices = utils.deepClone(this.options.prices);
        } else {
            _.each(additionalPrice, function (option, priceCode) {
                var origin = this.options.prices[priceCode] || {};
                var final = prices[priceCode] || {};
                option.amount = option.amount || 0;
                origin.amount = origin.amount || 0;
                origin.adjustments = origin.adjustments || {};
                final.adjustments = final.adjustments || {};

                final.amount = 0 + origin.amount + option.amount;
                _.each(option.adjustments, function (pa, paCode) {
                    final.adjustments[paCode] = 0 + (origin.adjustments[paCode] || 0) + pa;
                });
            }, this);
        }

        this.element.trigger('reloadPrice');
    }

    /**
     * Render price unit block.
     */
    function reDrawPrices() {
        /*jshint validthis: true */
        var box = this.element;
        var prices = this.cache.displayPrices;
        var priceFormat = this.options.priceConfig && this.options.priceConfig.priceFormat || {};
        var priceTemplate = hbs(this.options.priceTemplate);

        _.each(prices, function (price, priceCode) {
            var html,
                finalPrice = price.amount;
            _.each(price.adjustments, function (adjustmentAmount) {
                finalPrice += adjustmentAmount;
            });

            price.final = finalPrice;
            price.formatted = utils.formatPrice(finalPrice, priceFormat);

            html = priceTemplate(price);
            $('[data-price-type="' + priceCode + '"]', box).html(html);
        });
    }

    /**
     * Overwrites initial (default) prices object.
     * @param {Object} prices
     */
    function setDefaultPrices(prices) {
        /*jshint validthis: true */
        this.cache.displayPrices = utils.deepClone(prices);
        this.options.prices = utils.deepClone(prices);
    }

    /**
     * Custom behavior on getting options:
     * now widget able to deep merge of accepted configuration.
     * @param  {Object} options
     * @return {mage.priceBox}
     */
    function setOptions(options) {
        /*jshint validthis: true */
        $.extend(true, this.options, options);

        if ('disabled' in options) {
            this._setOption('disabled', options.disabled);
        }
        return this;
    }


    function setDefaultsFromDataSet() {
        /*jshint validthis: true */
        var box = this.element;
        var priceHolders = $('[data-price-type]', box);
        var prices = this.options.prices;
        this.options.productId = box.data('productId');
        if (_.isEmpty(prices)) {
            priceHolders.each(function (index, element) {
                var type = $(element).data('priceType');
                var amount = $(element).data('priceAmount');

                if(type && amount) {
                    prices[type] = {amount: amount};
                }
            });
        }
    }

    function setDefaultsFromPriceConfig() {
        /*jshint validthis: true */
        var config = this.options.priceConfig;
        if (config) {
            if (+config.productId !== +this.options.productId) {
                return;
            }
            this.options.prices = config.prices;
        }
    }
});