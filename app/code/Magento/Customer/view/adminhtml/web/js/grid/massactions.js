/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/massactions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate'
], function (Massactions, uiAlert, _, $, $t) {
    'use strict';

    return Massactions.extend({
        defaults: {
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                massaction: 'onAction'
            }
        },

        onAction: function (data) {
            if (data.action === 'delete') {
                this.source.reload({
                    refresh: true
                });
            }
        },

        defaultCallback: function (action, data) {
            var itemsType, selections;

            if (action.isAjax) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                selections = {};

                selections[itemsType] = data[itemsType];

                if (!selections[itemsType].length) {
                    selections[itemsType] = false;
                }

                _.extend(selections, data.params || {});

                console.log(selections);
                this.request(action.url, selections).done(function (response) {
                    if (!response.error) {
                        this.trigger('massaction', {
                            action: action.type,
                            data: this.selections().selected()
                        });
                    }
                }.bind(this));

            } else {
                this._super();
            }
        },

        request: function (href, data) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: data
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
