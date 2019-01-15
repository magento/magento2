/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'mage/translate'
    ],
    function ($, Component, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_AuthorizenetAcceptjs/payment/authorizenet-acceptjs',
                timeoutMessage: $t('Sorry, but something went wrong. Please contact the seller.')
            },
            placeOrderHandler: null,
            validateHandler: null,

            /**
             * @param {Object} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Object} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'authorizenet_directpost';
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            }
        });
    });