/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiElement',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, _, utils, Element, alert, $t) {
    'use strict';

    return Element.extend({
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
            utils.limit(this, 'reload', 300);
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

            this.setData({
                items: [],
                totalRecords: 0
            });

            return this;
        },

        /**
         *
         * @param {Object} data
         * @returns {Provider} Chainable.
         */
        setData: function (data) {
            data = this.processData(data);

            this.set('data', data);

            return this;
        },

        /**
         * Reloads data with current parameters.
         */
        reload: function () {
            this.trigger('reload');

            if (this.request && this.request.readyState !== 4) {
                this.request.abort();
            }

            this.request = $.ajax({
                url: this['update_url'],
                method: 'GET',
                data: this.get('params'),
                dataType: 'json'
            });

            this.request
                .done(this.onReload)
                .error(this.onError);
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
        onError: function (xhr) {
            if (xhr.statusText === 'abort') {
                return;
            }

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
            this.setData(data)
                .trigger('reloaded');
        }
    });
});
