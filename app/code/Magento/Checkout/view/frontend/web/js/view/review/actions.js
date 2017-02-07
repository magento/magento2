/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'underscore'
], function (Component, quote, _) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/review/actions',
            displayArea: 'actions'
        },

        /**
         * @return {*}
         */
        getActiveView: function () {
            var view = this.getViewByCode(quote.paymentMethod());

            return view ? view : this.getDefaultView();
        },

        /**
         * @param {*} code
         * @return {Object}
         */
        getViewByCode: function (code) {
            return _.find(this.elems(), function (elem) {
                return elem.index == code && !elem.isDefault; //eslint-disable-line eqeqeq
            });
        },

        /**
         * Get default view.
         *
         * @return {Object}
         */
        getDefaultView: function () {
            return _.find(this.elems(), function (elem) {
                return elem.isDefault;
            });
        }
    });
});
