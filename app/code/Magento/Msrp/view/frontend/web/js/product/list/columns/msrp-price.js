/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/product/list/columns/price-box',
    'Magento_Catalog/js/product/addtocart-button',
    'mage/dropdown'
], function ($, _, PriceBox) {
    'use strict';

    return PriceBox.extend({
        defaults: {
            priceBoxSelector: '[data-role=msrp-price-box]',
            popupTmpl: 'Magento_Msrp/product/item/popup',
            popupTriggerSelector: '[data-role=msrp-popup-trigger]',
            popupSelector: '[data-role=msrp-popup]',
            popupOptions: {
                appendTo: 'body',
                dialogContentClass: 'active',
                closeOnMouseLeave: false,
                autoPosition: true,
                dialogClass: 'popup map-popup-wrapper',
                position: {
                    my: 'left top',
                    collision: 'fit none',
                    at: 'left bottom',
                    within: 'body'
                },
                shadowHinter: 'popup popup-pointer'
            }
        },

        /**
         * Create and open popup with Msrp information.
         *
         * @param {Object} data - element data
         * @param {DOMElement} elem - element
         * @param {Event} event - event object
         */
        openPopup: function (data, elem, event) {
            var $elem = $(elem),
                $popup = $elem.find(this.popupSelector),
                $trigger = $elem.find(this.popupTriggerSelector);

            event.stopPropagation();

            this.popupOptions.position.of = $trigger;
            this.popupOptions.triggerTarget = $trigger;

            $popup.dropdownDialog(this.popupOptions)
                  .dropdownDialog('open');
        },

        /**
         * Set listeners.
         *
         * @param {DOMElement} elem - DOM element
         * @param {Object} data - element data
         */
        initListeners: function (elem, data) {
            var $trigger = $(elem).find(this.popupTriggerSelector);

            $trigger.on('click', this.openPopup.bind(this, data, elem));
        },

        /**
         * Check whether we can apply msrp, or should use standard price.
         *
         * @param {Object} row
         * @returns {Bool}
         */
        isMsrpApplicable: function (row) {
            return this.getPrice(row)['is_applicable'];
        },

        /**
         * Retrieve msrp formatted price
         *
         * @param {Object} row
         * @returns {String}
         */
        getPrice: function (row) {
            return row['price_info']['extension_attributes'].msrp;
        },

        /**
         * Returns path to the columns' body template.
         *
         * @returns {String}
         */
        getBody: function () {
            return this.bodyTmpl;
        },

        /**
         * Check if popup with actual price must be shown.
         *
         * @returns {Boolean}
         */
        isShowPriceOnGesture: function (row) {
            return this.getPrice(row)['is_shown_price_on_gesture'];
        },

        /**
         * Get msrp price supporting text.
         *
         * @returns {String}
         */
        getMsrpPriceMessage: function (row) {
            return this.getPrice(row)['msrp_message'];
        },

        /**
         * Get msrp price supporting text, when actual price is hidden.
         *
         * @returns {String}
         */
        getExplanationMessage: function (row) {
            return this.getPrice(row)['explanation_message'];
        }
    });
});
