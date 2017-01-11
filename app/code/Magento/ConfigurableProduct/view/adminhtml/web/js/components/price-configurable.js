/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function (_, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            listens: {
                isConfigurable: 'handlePriceValue'
            },
            imports: {
                isConfigurable: '!ns = ${ $.ns }, index = configurable-matrix:isEmpty'
            },
            modules: {
                createConfigurableButton: '${$.createConfigurableButton}'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();

            return this;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe(['content']);

            return this;
        },

        /**
         * Disable and clear price if product type changed to configurable
         *
         * @param {String} isConfigurable
         */
        handlePriceValue: function (isConfigurable) {
            if (isConfigurable) {
                this.disable();
                this.clear();
            } else {
                this.enable();
            }
        }
    });
});
