/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/columns/actions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate'
], function (Actions, uiAlert, _, $, $t) {
    'use strict';

    return Actions.extend({
        defaults: {
            ajaxSettings: {
                method: 'GET',
                dataType: 'json'
            },
            listens: {
                action: 'onAction'
            }
        },

        onAction: function (data) {
            if (data.action === 'delete') {
                this.source().reload({
                    refresh: true
                });
            }
        },

        /**
         * Default action callback. Redirects to
         * the specified in action's data url.
         *
         * @param {String} actionIndex - Action's identifier.
         * @param {(Number|String)} recordId - Id of the record associated
         *      with a specified action.
         * @param {Object} action - Action's data.
         */
        defaultCallback: function (actionIndex, recordId, action) {
            if (action.isAjax) {
                this.request(action.href).done(function (response) {
                    var data;

                    if (!response.error) {
                        data = _.findWhere(this.rows, {
                            _rowIndex: action.rowIndex
                        });

                        this.trigger('action', {
                            action: actionIndex,
                            data: data
                        });
                    }
                }.bind(this));

            } else {
                this._super();
            }
        },

        request: function (href) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href
            });

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    }
                })
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        }
    });
});
