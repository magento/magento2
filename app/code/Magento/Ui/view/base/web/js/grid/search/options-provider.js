/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'mageUtils',
    'uiComponent'
], function (_, $, utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            options: [],
            limit: 400,
            requestConfig: {
                url: '',
                dataType: 'json',
                method: 'GET',
                data: {}
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {OptionsProvider} Chainable.
         */
        initialize: function () {
            this._super();

            utils.limit(this, 'filter', this.limit);
            _.bindAll(this, '_setOptions');

            return this;
        },

        /**
         * Filters options data.
         *
         * @returns {OptionsProvider} Chainable.
         */
        filter: function (query) {
            this._getOptions(query)
                .then(this._setOptions);

            return this;
        },

        /**
         * Clears options data.
         *
         * @returns {OptionsProvider} Chainable.
         */
        clear: function () {
            this._setOptions([]);

            return this;
        },

        /**
         * Retrieves options data by sending 'GET' request.
         * If filter query is not specified, then request will
         * be resolved with an empty array.
         *
         * @param {String} [query] - Filter query.
         * @returns {jQueryPromise}
         */
        _getOptions: function (query) {
            var loaded = $.Deferred(),
                config = utils.copy(this.requestConfig);

            if (this.request) {
                this.request.abort();
            }

            if (!query) {
                loaded.resolve([]);
            } else {
                utils.extend(config, {
                    data: {
                        query: query
                    }
                });

                this.request = $.ajax(config);

                this.request.done(function (data) {
                    this.request = false;

                    loaded.resolve(data);
                });
            }

            return loaded.promise();
        },

        /**
         * Replaces options with a specified array.
         *
         * @param {Array} options - Options data.
         * @returns {OptionsProvider} Chainable.
         */
        _setOptions: function (options) {
            this.set('options', options);

            return this;
        }
    });
});
