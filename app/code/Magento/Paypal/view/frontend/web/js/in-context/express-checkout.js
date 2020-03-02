/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'uiComponent',
    'domReady!'
], function (_, $, Component) {
    'use strict';

    return Component.extend({

        defaults: {
            clientConfig: {

                checkoutInited: false,

                /**
                 * @param {Object} event
                 */
                click: function (event) {
                    $('body').trigger('processStart');

                    event.preventDefault();

                    if (!this.clientConfig.checkoutInited) {
                        this.clientConfig.checkoutInited = true;
                    }
                }
            }
        },

        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();

            return this.initClient();
        },

        /**
         * @returns {Object}
         */
        initClient: function () {
            _.each(this.clientConfig, function (fn, name) {
                if (typeof fn === 'function') {
                    this.clientConfig[name] = fn.bind(this);
                }
            }, this);

            return this;
        }
    });
});
