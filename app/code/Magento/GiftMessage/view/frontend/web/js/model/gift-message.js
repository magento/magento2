/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['Magento_Ui/js/lib/component/provider', 'underscore'],
    function (provider, _) {
        "use strict";
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
                initialize: function() {
                    this.getObservable('alreadyAdded')(false);
                    var message = false;

                    if (this.itemId == 'orderLevel') {
                        message = window.giftOptionsConfig.giftMessage[this.itemId];
                    } else {
                        message = window.giftOptionsConfig.giftMessage['itemLevel'][this.itemId];
                    }
                    if (_.isObject(message)) {
                        this.getObservable('recipient')(message.recipient);
                        this.getObservable('sender')(message.sender);
                        this.getObservable('message')(message.message);
                        this.getObservable('alreadyAdded')(true);
                    }
                },
                getObservable: function(key) {
                    this.initObservable(this.id, key);
                    return provider[this.getUniqueKey(this.id, key)];
                },
                initObservable: function(node, key) {
                    if (node && !this.observables.hasOwnProperty(node)) {
                        this.observables[node] = [];
                    }
                    if (key && this.observables[node].indexOf(key) == -1) {
                        this.observables[node].push(key);
                        provider.observe(this.getUniqueKey(node, key));
                    }
                },
                getUniqueKey: function(node, key) {
                    return node + '-' + key;
                },
                getConfigValue: function(key) {
                    return window.giftOptionsConfig.hasOwnProperty(key) ?
                        window.giftOptionsConfig[key]
                        : null;
                },
                reset: function() {
                    var self = this;
                    _.each(this.observables[this.id], function(key) {
                        provider[self.getUniqueKey(self.id, key)](null);
                    });
                    _.each(this.additionalOptions, function(option) {
                        if (_.isFunction(option.reset)) {
                            option.reset();
                        }
                    });
                    this.getObservable('isClear')(true);
                },
                getAfterSubmitCallbacks: function() {
                    var callbacks = [];
                    _.each(this.additionalOptions, function(option) {
                        if (_.isFunction(option.afterSubmit)) {
                            callbacks.push(option.afterSubmit);
                        }
                    });
                    return callbacks;
                },
                getSubmitParams: function() {
                    var params = {},
                        self = this;
                    _.each(this.submitParams, function(key) {
                        params[key] = provider[self.getUniqueKey(self.id, key)]();
                    });

                    if(this.additionalOptions.length) {
                        params['extension_attributes'] = {};
                    }
                    _.each(this.additionalOptions, function(option) {
                        if (_.isFunction(option.getSubmitParams)) {
                            params['extension_attributes'] = _.extend(
                                params['extension_attributes'],
                                option.getSubmitParams(self.itemId)
                            );
                        }
                    });
                    return params;
                }
            };
            model.initialize();
            return model;
        }
    }
);
