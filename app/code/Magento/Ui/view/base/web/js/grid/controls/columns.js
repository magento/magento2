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
            viewportSize: 18,
            viewportMaxSize: 30,
            headerMessage: $t('<%- visible %> out of <%- total %> visible')
        },

        /**
         * Action Reset
         */
        reset: function () {
            this.delegate('resetVisible');
        },

        /**
         * Action Apply
         */
        apply: function () {
            var data = {},
                current;

            this.close();

            current = this.source.get('config.columns') || {};

            this.elems().forEach(function (elem) {
                data[elem.index] = {
                    visible: elem.visible()
                };
            });

            utils.extend(current, data);

            this.source.store('config.columns', current);
        },

        /**
         * Action Cancel
         */
        cancel: function () {
            var previous = this.source.get('config.columns'),
                config;

            this.close();

            if (!previous) {
                return;
            }

            this.elems().forEach(function (elem) {
                config = previous[elem.index] || {};

                elem.visible(config.visible);
            });
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
            var count = this.countVisible(),
                isLast = elem.visible() && count === 1,
                isTooMuch = count > this.viewportMaxSize;

            return isLast || isTooMuch;
        },

        /**
         * Helper, returns number of visible checkboxes
         * @returns {Number}
         */
        countVisible: function () {
            return this.elems().filter(function (elem) {
                return elem.visible();
            }).length;
        },

        /**
         * Compile header message from headerMessage setting.
         * Expects Underscore template format
         * @returns {String}
         */
        getHeaderMessage: function () {
            return _.template(this.headerMessage, {
                visible: this.countVisible(),
                total: this.elems().length
            });
        }
    });
});
