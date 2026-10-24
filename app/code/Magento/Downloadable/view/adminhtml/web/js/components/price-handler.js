/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
define([
    'Magento_Ui/js/form/element/abstract'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            linksPurchasedSeparately: '0',
            useDefaultPrice: false,
            listens: {
                linksPurchasedSeparately: 'changeDisabledStatus',
                useDefaultPrice: 'changeDisabledStatus'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            this.changeDisabledStatus();

            return this;
        },

        /**
         * Disable/enable price field
         */
        changeDisabledStatus: function () {
            if (this.linksPurchasedSeparately === '1') {
                if (this.useDefaultPrice) {
                    this.disabled(true);
                } else {
                    this.disabled(false);
                }
            } else {
                this.disabled(true);
            }
        }
    });
});
