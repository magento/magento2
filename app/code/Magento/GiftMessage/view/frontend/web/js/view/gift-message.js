/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
        'uiComponent',
        'Magento_GiftMessage/js/model/gift-message',
        'Magento_GiftMessage/js/model/gift-options',
        'Magento_GiftMessage/js/action/gift-options'
    ],
    function (Component, giftMessage, giftOptions, giftOptionsService) {
        "use strict";
        return Component.extend({
            formBlockVisibility: null,
            resultBlockVisibility: null,
            model: {},
            initialize: function() {
                var self = this;
                this._super()
                    .observe('formBlockVisibility')
                    .observe({'resultBlockVisibility': false});

                this.itemId = this.itemId || 'orderLevel';
                var model = new giftMessage(this.itemId);
                giftOptions.addOption(model);
                this.model = model;

                this.model.getObservable('isClear').subscribe(function(value) {
                    if (value == true) {
                        self.formBlockVisibility(false);
                        self.model.getObservable('alreadyAdded')(true);
                    }
                });

                this.isResultBlockVisible();
            },
            isResultBlockVisible: function() {
                var self = this;
                if (this.model.getObservable('alreadyAdded')()) {
                    this.resultBlockVisibility(true);
                }
                this.model.getObservable('additionalOptionsApplied').subscribe(function(value) {
                    if (value == true) {
                        self.resultBlockVisibility(true);
                    }
                });
            },
            getObservable: function(key) {
                return this.model.getObservable(key);
            },
            toggleFormBlockVisibility: function() {
                if (!this.model.getObservable('alreadyAdded')()) {
                    this.formBlockVisibility(!this.formBlockVisibility());
                }
            },
            editOptions: function() {
                this.resultBlockVisibility(false);
                this.formBlockVisibility(true);
            },
            deleteOptions: function() {
                giftOptionsService(this.model, true);
            },
            hideFormBlock: function() {
                this.formBlockVisibility(false);
                if (this.model.getObservable('alreadyAdded')()) {
                    this.resultBlockVisibility(true);
                }
            },
            hasActiveOptions: function() {
                var regionData = this.getRegion('additionalOptions');
                var options = regionData();
                for (var i in options) {
                    if (options[i].isActive()) {
                        return true;
                    }
                }
                return false;
            },
            isActive: function() {
                switch (this.itemId) {
                    case 'orderLevel':
                        return this.model.getConfigValue('isOrderLevelGiftOptionsEnabled') == true;
                    default:
                        return this.model.getConfigValue('isItemLevelGiftOptionsEnabled') == true;
                }
            },
            submitOptions: function() {
                giftOptionsService(this.model);
            }
        });
    }
);
