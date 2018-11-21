/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/form/form',
    'underscore',
    'mageUtils'
], function ($, Form, _, utils) {
    'use strict';

    return Form.extend({
        defaults: {
            sourceSelectionUrl: '${ $.sourceSelectionUrl }'
        },

        /**
         * Process source selection algorithm
         *
         * @param {String} redirect
         * @param {Object} data
         */
        processAlgorithm: function (redirect, data) {
            var formData = utils.filterFormData(this.source.get('data'));

            data.requestData = [];

            _.each(formData.items, function (item) {
                data.requestData.push({
                    orderItem: item.orderItemId,
                    sku: item.sku,
                    qty: item.qtyToShip
                });
            });

            data.orderId = formData['order_id'];
            data.websiteId = formData.websiteId;
            data = utils.serialize(utils.filterFormData(data));
            data['form_key'] = window.FORM_KEY;

            if (!this.sourceSelectionUrl || this.sourceSelectionUrl === 'undefined') {
                return $.Deferred.resolve();
            }

            $('body').trigger('processStart');

            $.ajax({
                url: this.sourceSelectionUrl,
                data: data,

                /**
                 * Success callback.
                 *
                 * @param {Object} response
                 */
                success: function (response) {
                    var dataItems = this.source.get('data.items');

                    _.each(dataItems, function (item) {
                        if (response[item.orderItemId]) {
                            this.source.set('data.items.' + item['record_id'] + '.sources', response[item.orderItemId]);
                        }
                    }.bind(this));
                    this.source.trigger('reInitSources');
                    this.source.set('data.sourceCodes', response.sourceCodes ? response.sourceCodes : []);
                }.bind(this),

                /**
                 * Complete callback.
                 */
                complete: function () {
                    $('body').trigger('processStop');
                }
            });
        }
    });
});
