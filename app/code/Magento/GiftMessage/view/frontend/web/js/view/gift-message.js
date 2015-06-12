/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', '../model/gift-message', '../model/gift-options', '../action/gift-options'],
    function (Component, giftMessage, giftOptions, giftOptionsService) {
        "use strict";
        return Component.extend({
            formBlockVisibility: null,
            resultBlockVisibility: false,
            model: {},
            initialize: function() {
                this._super()
                    .observe('formBlockVisibility')
                    .observe({'resultBlockVisibility': false});

                this.itemId = this.itemId || 'orderLevel';
                var model = new giftMessage(this.itemId);
                giftOptions.addOption(model);
                this.model = model;
                if (this.model.getObservable('alreadyAdded')()) {
                    this.resultBlockVisibility(true);
                }
                var self = this;
                this.model.getObservable('isClear').subscribe(function(value) {
                    if (value == true) {
                        self.formBlockVisibility(false);
                        self.model.getObservable('alreadyAdded')(true);
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
                this.model.reset();
                giftOptionsService(this.model);
            },
            hideFormBlock: function() {
                this.formBlockVisibility(false);
                if (this.model.getObservable('alreadyAdded')()) {
                    this.resultBlockVisibility(true);
                }
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
