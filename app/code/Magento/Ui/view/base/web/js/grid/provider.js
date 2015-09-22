/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, _, utils, Component, alert, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            listens: {
                params: 'reload'
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            utils.limit(this, 'reload', 200);
            _.bindAll(this, 'onReload');

            return this._super();
        },

        /**
         * Initializes provider config.
         *
         * @returns {Provider} Chainable.
         */
        initConfig: function () {
            this._super();

            _.extend(this.data, {
                items: [],
                totalRecords: 0
            });

            return this;
        },

        /**
         * Reloads data with current parameters.
         */
        reload: function () {
            this.trigger('reload');

            $.ajax({
                url: this.update_url,
                method: 'GET',
                data: this.get('params'),
                dataType: 'json'
            })
            .error(this.onError)
            .done(this.onReload);
        },

        /**
         * Processes data before applying it.
         *
         * @param {Object} data - Data to be processed.
         * @returns {Object}
         */
        processData: function (data) {
            var items = data.items;

            _.each(items, function (record, index) {
                record._rowIndex = index;
            });

            return data;
        },

        /**
         * Handles reload error.
         */
        onError: function () {
            alert({
                content: $t('Something went wrong.')
            });
        },

        /**
         * Handles successful data reload.
         *
         * @param {Object} data - Retrieved data object.
         */
        onReload: function (data) {
            data = this.processData(data);

            this.set('data', data)
                .trigger('reloaded');
        }
    });
});
