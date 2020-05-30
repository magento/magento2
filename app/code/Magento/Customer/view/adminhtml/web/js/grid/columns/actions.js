/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                action: 'onAction'
            },
            ignoreTmpls: {
                fieldAction: true,
                options: true,
                action: true
            }
        },

        /**
         * Reload customer address listing data source after customer address delete action
         *
         * @param {Object} data
         */
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

        /**
         * Send customer address listing ajax request
         *
         * @param {String} href
         */
        request: function (href) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: {
                    'form_key': window.FORM_KEY
                }
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
