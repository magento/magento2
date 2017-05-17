/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_GiftMessage/js/model/url-builder',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/error-processor',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'underscore'
], function (urlBuilder, storage, messageList, errorProcessor, url, quote, _) {
    'use strict';

    return function (giftMessage, remove) {
        var serviceUrl;

        url.setBaseUrl(giftMessage.getConfigValue('baseUrl'));

        if (giftMessage.getConfigValue('isCustomerLoggedIn')) {
            serviceUrl = urlBuilder.createUrl('/carts/mine/gift-message', {});

            if (giftMessage.itemId != 'orderLevel') { //eslint-disable-line eqeqeq
                serviceUrl = urlBuilder.createUrl('/carts/mine/gift-message/:itemId', {
                    itemId: giftMessage.itemId
                });
            }
        } else {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/gift-message', {
                cartId: quote.getQuoteId()
            });

            if (giftMessage.itemId != 'orderLevel') { //eslint-disable-line eqeqeq
                serviceUrl = urlBuilder.createUrl(
                    '/guest-carts/:cartId/gift-message/:itemId',
                    {
                        cartId: quote.getQuoteId(), itemId: giftMessage.itemId
                    }
                );
            }
        }
        messageList.clear();

        storage.post(
            serviceUrl,
            JSON.stringify({
                'gift_message': giftMessage.getSubmitParams(remove)
            })
        ).done(function () {
            giftMessage.reset();
            _.each(giftMessage.getAfterSubmitCallbacks(), function (callback) {
                if (_.isFunction(callback)) {
                    callback();
                }
            });
        }).fail(function (response) {
            errorProcessor.process(response);
        });
    };
});
