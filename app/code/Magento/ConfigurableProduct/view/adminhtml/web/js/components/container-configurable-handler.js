/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            listens: {
                '${ $.provider }:data.is_downloadable': 'handleProductType'
            },
            links: {
                isDownloadable: '${ $.provider }:data.is_downloadable'
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
            this.handleProductType(this.isDownloadable);

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
         * Change content for container and visibility for button
         *
         * @param {String} isDownloadable
         */
        handleProductType: function (isDownloadable) {
            if (isDownloadable === '1') {
                this.content(this.content2);

                if (this.createConfigurableButton()) {
                    this.createConfigurableButton().visible(false);
                }
            } else {
                this.content(this.content1);

                if (this.createConfigurableButton()) {
                    this.createConfigurableButton().visible(true);
                }
            }
        }
    });
});
