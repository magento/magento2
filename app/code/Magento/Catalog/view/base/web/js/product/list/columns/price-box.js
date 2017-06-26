/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'uiRegistry',
    'mageUtils',
    'uiCollection',
    'Magento_Catalog/js/product/list/column-status-validator',
    'uiLayout'
], function (ko, _, registry, utils, Collection, columnStatusValidator, layout) {
    'use strict';

    return Collection.extend({
        defaults: {
            label: '',
            hasSpecialPrice: false,
            showMinimalPrice: false,
            useLinkForAsLowAs: false,
            visible: true,
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'Magento_Catalog/product/price/price_box',
            disableAction: false,
            controlVisibility: true,
            sortable: false,
            sorting: false,
            draggable: true,
            fieldClass: {},
            renders: {
                default: {}
            },
            ignoreTmpls: {
                fieldAction: true
            },
            statefull: {
                visible: true,
                sorting: true
            },
            imports: {
                exportSorting: 'sorting'
            },
            listens: {
                elems: ''
            },
            modules: {
                source: '${ $.provider }'
            },
            pricesInit: {}
        },

        /**
         * Sort prices api
         *
         * @returns {exports}
         */
        sort: function () {
            return this;
        },

        /**
         * Check whether is allowed to render price or not
         *
         * @returns {*}
         */
        isAllowed: function () {
            return columnStatusValidator.isValid(this.source(), 'price', 'show_attributes');
        },

        /**
         * Retrieve array of prices, that should be rendered for specific product
         *
         * @param {Array} row
         * @return {Array}
         */
        getPrices: function (row) {
            var elems = this.elems() ? this.elems() : ko.getObservable(this, 'elems'),
                result;

            //we cant take type of product from row
            this.initPrices(row);
            result = _.filter(elems, function (elem) {
                return elem.productType === row.type;
            });

            return result;
        },

        /**
         * Recursive Merging of objects
         *
         * @param {Array} target
         * @param {Array} source
         * @returns {Array}
         * @private
         */
        _deepObjectExtend: function (target, source) {
            var _target = utils.copy(target);

            _.each(source, function (value, key) {
                if (_.keys(value).length && typeof _target[key] !== 'undefined') {
                    _target[key] = this._deepObjectExtend(_target[key], value);
                } else {
                    _target[key] = value;
                }
            }, this);

            return _target;
        },

        /**
         * Init price type box, in cases when product type has custom component or bodyTmpl
         *
         * @param {String} productType
         * @private
         */
        _initPriceWithCustomMetaData: function (productType) {
            var price = this._deepObjectExtend(
                this.renders.prices['default'],
                this.renders.prices[productType]
            );

            price.name = productType + '.default';
            price.parent = this.name;
            price.source = this.source;
            price.productType = productType;
            layout([price]);
        },

        /**
         * Init Prices by product type and add them to layout
         *
         * @param {Array} _priceData
         * @param {String} productType
         * @private
         */
        _initPricesForProductType: function (_priceData, productType) {
            var prices = [];

            this._setPriceNamesToPrices(_priceData, productType);
            _.sortBy(_priceData, this._comparePrices);

            _.each(_priceData, function (priceData) {
                if (!priceData.component) {
                    return;
                }

                priceData.parent = this.name;
                priceData.provider = this.provider;
                priceData.productType = productType;
                priceData = utils.template(priceData, this);
                prices.push(priceData);
            }, this);

            layout(prices);
        },

        /**
         * Init dynamic price components
         *
         * @param {Array} row
         * @returns {void}
         */
        initPrices: function (row) {
            var _priceData = [],
                productType = row.type,
                defaultPrice = this.renders.prices['default'];

            if (this.pricesInit[productType]) {
                return true;
            }

            this.pricesInit[productType] = true;

            if (this.renders.prices[productType] && this._needToApplyCustomTemplate(this.renders.prices[productType])) {
                return this._initPriceWithCustomMetaData(productType);
            }

            if (this.renders.prices[productType] && this.renders.prices[productType].children) {
                _priceData = this._deepObjectExtend(defaultPrice.children, this.renders.prices[productType].children);
            } else {
                _priceData = defaultPrice.children;
            }

            return this._initPricesForProductType(_priceData, productType);
        },

        /**
         * Set name to all price components
         *
         * @param {Array} prices
         * @param {String} productType
         * @private
         */
        _setPriceNamesToPrices: function (prices, productType) {
            _.each(prices, function (price, name) {
                price.priceType = name;
                price.name = name + '.' + productType;
            });

            return prices;
        },

        /**
         * Sort callback to compare prices by sort order
         *
         * @param {Number} firstPrice
         * @param {Number} secondPrice
         * @returns {Number}
         * @private
         */
        _comparePrices: function (firstPrice, secondPrice) {
            if (firstPrice.sortOrder < secondPrice.sortOrder) {
                return -1;
            }

            if (firstPrice.sortOrder > secondPrice.sortOrder) {
                return 1;
            }

            return 0;
        },

        /**
         * Check whether metadata of product type prices was changed, and we should
         * to apply custom template or custom component
         *
         * @param {Array} productData
         * @returns {*}
         * @private
         */
        _needToApplyCustomTemplate: function (productData) {
            return productData.bodyTmpl || productData.component;
        },

        /**
         * Returns path to the columns' body template.
         *
         * @returns {String}
         */
        getBody: function () {
            return this.bodyTmpl;
        },

        /**
         * Get price label.
         *
         * @returns {String}
         */
        getLabel: function () {
            return this.label;
        }
    });
});
