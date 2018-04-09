/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/form/form',
    'mageUtils'
], function ($, Form, utils) {
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
                 * @param {Object} response
                 * @returns {Boolean}
                 */
                success: function (response) {
                    //TODO: also, need to update sourceCodes select
                    var formData = this.source.get('data');
                    _.each(formData.items, function (item) {
                        if (response[item.orderItemId]) {
                            //TODO: this feature doesn't work
                            //TODO: rebuild select field sourceCode
                            this.source.set('data.items.' + item['record_id'] + '.sources', response[item.orderItemId]);
                        }
                    }.bind(this));
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
