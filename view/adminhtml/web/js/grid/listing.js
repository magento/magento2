/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_AdminNotification/js/grid/listing',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'underscore',
    'jquery'
], function (Listing, uiAlert, $t, _, $) {
    'use strict';

    return Listing.extend({
        defaults: {
            isAllowed: true,
            ajaxSettings: {
                method: 'POST',
                data: {},
                url: '${ $.dismissUrl }'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            _.bindAll(this, 'reload', 'onError');

            return this._super();
        },

        /**
         * Dismiss all items.
         */
        dismissAll: function () {
            var toDismiss = [];

            _.each(this.rows, function (row) {
                if (row.dismiss) {
                    toDismiss.push(row.uuid);
                }
            });
            toDismiss.length && this.dismiss(toDismiss);
        },

        /**
         * Dismiss action.
         *
         * @param {Array} items
         */
        dismiss: function (items) {
            var config = _.extend({}, this.ajaxSettings);

            config.data.uuid = items;
            this.showLoader();

            $.ajax(config)
                .done(this.reload)
                .fail(this.onError);
        },

        /**
         * Success callback for dismiss request.
         */
        reload: function () {
            this.source.reload({
                refresh: true
            });
        },

        /**
         * Error callback for dismiss request.
         *
         * @param {Object} xhr
         */
        onError: function (xhr) {
            this.hideLoader();

            if (xhr.statusText === 'abort') {
                return;
            }

            uiAlert({
                content: $t('Something went wrong.')
            });
        }
    });
});
