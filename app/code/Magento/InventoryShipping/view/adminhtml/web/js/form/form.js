/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/form/form',
    'mageUtils',
    'uiRegistry'
], function ($, Form, utils, registry) {
    'use strict';

    return Form.extend({
        defaults: {
            sourceSelectionUrl: '${ $.sourceSelectionUrl }'
        },

        /**
         * Validate and save form.
         *
         * @param {String} redirect
         * @param {Object} data
         */
        processAlgorithm: function (redirect, data) {
            var process = $.Deferred(),
                self = this,
                formData = utils.filterFormData(this.source.get('data')),
                requestData = [];

            _.each(formData.items, function (item) {
                requestData.push({
                    orderItem: item.orderItemId,
                    sku: item.sku,
                    qty: item.qtyToShip
                });
            });

            data.websiteId = formData.websiteId;
            data.requestData = requestData;
            data = utils.serialize(utils.filterFormData(data));
            data['form_key'] = window.FORM_KEY;

            if (!this.sourceSelectionUrl || this.sourceSelectionUrl === 'undefined') {
                return process.resolve();
            }

            $('body').trigger('processStart');

            $.ajax({
                url: this.sourceSelectionUrl,
                data: data,

                /**
                 * Success callback.
                 * @param {Object} resp
                 * @returns {Boolean}
                 */
                success: function (resp) {
                    if (!resp.error) {
                        //TODO: also, need to update sourceCodes select
                        var formData = self.source.get('data');
                        _.each(formData.items, function (item) {
                            if (resp.items[item.orderItemId]) {
                                //TODO: this feature doesn't work
                                self.source.set('data.items.' + item['record_id'] + '.sources', resp.items[item.orderItemId]);
                            }
                        });

                        process.resolve();

                        return true;
                    }

                    $('body').notification('clear');
                    $.each(resp.messages || [resp.message] || [], function (key, message) {
                        $('body').notification('add', {
                            error: resp.error,
                            message: message,

                            /**
                             * Insert method.
                             *
                             * @param {String} msg
                             */
                            insertMethod: function (msg) {
                                var $wrapper = $('<div/>').addClass(messagesClass).html(msg);

                                $('.page-main-actions', selectorPrefix).after($wrapper);
                            }
                        });
                    });
                },

                /**
                 * Complete callback.
                 */
                complete: function () {
                    $('body').trigger('processStop');
                }
            });

            return process.promise();
        }
    });
});
