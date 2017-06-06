/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'Magento_Ui/js/grid/listing'
], function (ko, _, Listing) {
    'use strict';

    return Listing.extend({
        defaults: {
            additionalClasses: '',
            filteredRows: [],
            limit: 5,
            shouldMergeFromSource: ['displayMode', 'additionalClasses'],
            listens: {
                elems: 'filterRows'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.initializeListingConfig()
                .initProductsLimit();
            this.hideLoader();
        },

        /**
         * Initialize product limit
         * Product limit can be configured through Ui component.
         * Product limit are present in widget form
         *
         * @returns {exports}
         */
        initProductsLimit: function () {
            if (this.source['page_size']) {
                this.limit = this.source['page_size'];
            }

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Listing} Chainable.
         */
        initObservable: function () {
            this._super()
                .track({
                    rows: []
                });

            return this;
        },

        /**
         * Initialize all configs, that are required for product listing ui component
         * @returns {this}
         */
        initializeListingConfig: function () {
            var listingSource = this.source.listing;

            _.each(this.shouldMergeFromSource, function (attributeToMerge) {
                if (listingSource.hasOwnProperty(attributeToMerge)) {
                    this[attributeToMerge] = listingSource[attributeToMerge];
                }
            }, this);

            return this;
        },

        /**
         * Sort rows by their action time and filter by allowed limit
         *
         * @return void
         */
        filterRows: function () {
            this.filteredRows = _.sortBy(this.rows, 'added_at').reverse().slice(0, this.limit);
        },

        /**
         * Can retrieve product url
         *
         * @param {Object} row
         * @returns {String}
         */
        getUrl: function (row) {
            return row.url;
        },

        /**
         * Get product attribute by code.
         *
         * @param {String} code
         * @return {Object}
         */
        getComponentByCode: function (code) {
            var elems = this.elems() ? this.elems() : ko.getObservable(this, 'elems'),
                component;

            component = _.filter(elems, function (elem) {
                return elem.index === code;
            }, this).pop();

            return component;
        }
    });
});
