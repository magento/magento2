/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiElement',
    './client'
], function (_, Element, Client) {
    'use strict';

    return Element.extend({
        defaults: {
            clientConfig: {
                urls: {
                    save: '${ $.submit_url }',
                    beforeSave: '${ $.validate_url }'
                }
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            this._super()
                .initClient();

            return this;
        },

        /**
         * Initializes client component.
         *
         * @returns {Provider} Chainable.
         */
        initClient: function () {
            this.client = new Client(this.clientConfig);

            return this;
        },

        /**
         * Saves currently available data.
         *
         * @param {Object} [options] - Addtitional request options.
         * @returns {Provider} Chainable.
         */
        save: function (options) {
            var data = this.get('data');

            this.client.save(data, options);

            return this;
        }
    });
});
