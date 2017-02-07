/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'underscore',
    'mage/url'
], function (uiElement, _, url) {
    'use strict';

    var provider = uiElement();

    return function (itemId) {
        var model = {
            id: 'message-' + itemId,
            itemId: itemId,
            observables: {},
            additionalOptions: [],
            submitParams: [
                'recipient',
                'sender',
                'message'
            ],

            /**
             * Initialize.
             */
            initialize: function () {
                var message = false;

                this.getObservable('alreadyAdded')(false);

                if (this.itemId == 'orderLevel') { //eslint-disable-line eqeqeq
                    message = window.giftOptionsConfig.giftMessage.hasOwnProperty(this.itemId) ?
                        window.giftOptionsConfig.giftMessage[this.itemId] :
                        null;
                } else {
                    message =
                        window.giftOptionsConfig.giftMessage.hasOwnProperty('itemLevel') &&
                        window.giftOptionsConfig.giftMessage.itemLevel.hasOwnProperty(this.itemId) ?
                            window.giftOptionsConfig.giftMessage.itemLevel[this.itemId].message :
                            null;
                }

                if (_.isObject(message)) {
                    this.getObservable('recipient')(message.recipient);
                    this.getObservable('sender')(message.sender);
                    this.getObservable('message')(message.message);
                    this.getObservable('alreadyAdded')(true);
                }
            },

            /**
             * @param {String} key
             * @return {*}
             */
            getObservable: function (key) {
                this.initObservable(this.id, key);

                return provider[this.getUniqueKey(this.id, key)];
            },

            /**
             * @param {String} node
             * @param {String} key
             */
            initObservable: function (node, key) {
                if (node && !this.observables.hasOwnProperty(node)) {
                    this.observables[node] = [];
                }

                if (key && this.observables[node].indexOf(key) === -1) {
                    this.observables[node].push(key);
                    provider.observe(this.getUniqueKey(node, key));
                }
            },

            /**
             * @param {String} node
             * @param {String} key
             * @return {String}
             */
            getUniqueKey: function (node, key) {
                return node + '-' + key;
            },

            /**
             * @param {String} key
             * @return {null}
             */
            getConfigValue: function (key) {
                return window.giftOptionsConfig.hasOwnProperty(key) ?
                    window.giftOptionsConfig[key]
                    : null;
            },

            /**
             * Reset.
             */
            reset: function () {
                this.getObservable('isClear')(true);
            },

            /**
             * @return {Array}
             */
            getAfterSubmitCallbacks: function () {
                var callbacks = [];

                callbacks.push(this.afterSubmit);
                _.each(this.additionalOptions, function (option) {
                    if (_.isFunction(option.afterSubmit)) {
                        callbacks.push(option.afterSubmit);
                    }
                });

                return callbacks;
            },

            /**
             * After submit.
             */
            afterSubmit: function () {
                window.location.href = url.build('checkout/cart/updatePost') +
                    '?form_key=' + window.giftOptionsConfig.giftMessage.formKey +
                    '&cart[]';
            },

            /**
             * @param {Boolean} remove
             * @return {Object}
             */
            getSubmitParams: function (remove) {
                var params = {},
                    self = this;

                _.each(this.submitParams, function (key) {
                    var observable = provider[self.getUniqueKey(self.id, key)];

                    if (_.isFunction(observable)) {
                        params[key] = remove ? null : observable();
                    }
                });

                if (this.additionalOptions.length) {
                    params['extension_attributes'] = {};
                }
                _.each(this.additionalOptions, function (option) {
                    if (_.isFunction(option.getSubmitParams)) {
                        params['extension_attributes'] = _.extend(
                            params['extension_attributes'],
                            option.getSubmitParams(remove)
                        );
                    }
                });

                return params;
            },

            /**
             * Check if gift message can be displayed
             *
             * @returns {Boolean}
             */
            isGiftMessageAvailable: function () {
                var isGloballyAvailable,
                    giftMessageConfig,
                    itemConfig;

                // itemId represent gift message level: 'orderLevel' constant or cart item ID
                if (this.itemId === 'orderLevel') {
                    return this.getConfigValue('isOrderLevelGiftOptionsEnabled');
                }

                // gift message product configuration must override system configuration
                isGloballyAvailable = this.getConfigValue('isItemLevelGiftOptionsEnabled');
                giftMessageConfig = window.giftOptionsConfig.giftMessage;
                itemConfig = giftMessageConfig.hasOwnProperty('itemLevel') &&
                    giftMessageConfig.itemLevel.hasOwnProperty(this.itemId) ?
                    giftMessageConfig.itemLevel[this.itemId] :
                    {};

                return itemConfig.hasOwnProperty('is_available') ? itemConfig['is_available'] : isGloballyAvailable;
            }
        };

        model.initialize();

        return model;
    };
});
