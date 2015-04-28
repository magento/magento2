/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    'mage/translate',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (utils, $t, _, Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/columns',
            minVisible: 1,
            maxVisible: 30,
            viewportSize: 18
        },

        /**
         * Action Reset
         */
        reset: function () {
            this.elems.each('applyState', 'visible', 'default');

            return this;
        },

        /**
         * Action Cancel
         */
        cancel: function () {
            this.close()
                .elems.each('applyState', 'visible', 'last');

            return this;
        },

        /**
         * Helper, wich helps to stop resizing and
         * @returns {Boolean}
         */
        hasOverflow: function () {
            return this.elems().length > this.viewportSize;
        },

        /**
         * Helper, checks
         *  - if less than one item choosen
         *  - if more then viewportMaxSize choosen
         * @param {Object} elem
         * @returns {Boolean}
         */
        isDisabled: function (elem) {
            var visible = this.countVisible();

            return elem.visible() ?
                visible === this.minVisible :
                visible === this.maxVisible;
        },

        /**
         * Helper, returns number of visible checkboxes
         * @returns {Number}
         */
        countVisible: function () {
            return this.elems.filter('visible').length;
        },

        /**
         * Compile header message from headerMessage setting.
         * Expects Underscore template format
         * @param {String} text - underscore-format template
         * @returns {String}
         */
        getHeaderMessage: function (text) {
            return _.template(text)({
                visible: this.countVisible(),
                total: this.elems().length
            });
        }
    });
});
