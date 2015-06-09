/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'mage/translate',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, $t, Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/columns',
            minVisible: 1,
            maxVisible: 30,
            viewportSize: 18,
            columnsData: {
                container: 'elems'
            },
            imports: {
                addColumns: '${ $.columnsData.provider }:${ $.columnsData.container }'
            },
            templates: {
                headerMsg: $t('${ $.visible } out of ${ $.total } visible')
            }
        },

        /**
         * Resets columns visibility to theirs default state.
         *
         * @returns {Columns} Chainable.
         */
        reset: function () {
            this.elems.each('applyState', 'default', 'visible');

            return this;
        },

        /**
         * Applies last saved state of columns visibility.
         *
         * @returns {Columns} Chainable.
         */
        cancel: function () {
            this.elems.each('applyState', 'saved', 'visible');

            return this;
        },

        /**
         * Adds columns whose visibility can be controlled to the component.
         *
         * @param {Array} columns - Elements array that will be added to component.
         * @returns {Columns} Chainable.
         */
        addColumns: function (columns) {
            columns = _.where(columns, {
                controlVisibility: true
            });

            this.insertChild(columns);

            return this;
        },

        /**
         * Defines whether child elements array length
         * is greater than the 'viewportSize' property.
         *
         * @returns {Boolean}
         */
        hasOverflow: function () {
            return this.elems().length > this.viewportSize;
        },

        /**
         * Helper, checks
         *  - if less than one item choosen
         *  - if more then viewportMaxSize choosen
         *
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
         * Counts number of visible columns.
         *
         * @returns {Number}
         */
        countVisible: function () {
            return this.elems.filter('visible').length;
        },

        /**
         * Compile header message from headerMessage setting.
         *
         * @returns {String}
         */
        getHeaderMessage: function () {
            return utils.template(this.templates.headerMsg, {
                visible: this.countVisible(),
                total: this.elems().length
            });
        }
    });
});
