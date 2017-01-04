/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery',
    'Magento_Ui/js/form/provider',
    'Magento_Ui/js/modal/alert'
], function ($, Provider, alert) {
    'use strict';

    return Provider.extend({
        defaults: {
            clientConfig: {
                urls: {
                    postpone: '${ $.postpone_url }'
                }
            }
        },

        /**
         * Send request to postpone component appearance for a certain time.
         *
         * @param {Object} [options] - Addtitional request options.
         * @returns {Provider} Chainable.
         */
        postponeRequest: function (options) {
            var url = this.client.urls.postpone,
                data = this.data;

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                showLoader: true,

                /** @inheritdoc */
                success: function (xhr) {
                    if (xhr.error) {
                        alert({
                            content: xhr.message
                        });
                    }
                },

                /** @inheritdoc */
                error: function (xhr) {
                    if (xhr.statusText === 'abort') {
                        return;
                    }

                    alert({
                        content: 'Something went wrong.'
                    });
                }
            }, options);

            return this;
        }
    });
});
