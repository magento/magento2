/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/listing',
    'Magento_Ui/js/lib/spinner',
    'jquery'
], function (Listing, loader, $) {
    'use strict';

    return Listing.extend({
        defaults: {
            imports: {
                totalRecords: '${ $.provider }:data.totalRecords'
            },
            selectors: {
                collapsible: '.message-system-collapsible',
                messages: '.message-system'
            }
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .track({
                    totalRecords: 0
                });

            return this;
        },

        /** @inheritdoc */
        showLoader: function () {
            if (!this.source.firstLoad) {
                this.fixLoaderHeight();
                this._super();
            }
        },

        /**
         * Calculates loader height
         *
         * @param {Boolean} [closed]
         */
        fixLoaderHeight: function (closed) {
            var $messagesBlock = $(this.selectors.messages),
                $collapsibleBlock = $(this.selectors.collapsible),
                resultHeight = 0;

            if ($messagesBlock.length) {
                resultHeight += $messagesBlock.outerHeight();
            }

            if ($collapsibleBlock.length && $collapsibleBlock.is(':visible') && !closed) {
                resultHeight += $collapsibleBlock.outerHeight();
            }

            loader.get(this.name).height(resultHeight);
        }
    });
});
