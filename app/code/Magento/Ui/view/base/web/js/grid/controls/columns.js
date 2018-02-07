/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'mage/translate',
    'uiCollection'
], function (_, utils, $t, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'ui/grid/controls/columns',
            minVisible: 1,
            maxVisible: 30,
            viewportSize: 18,
            displayArea: 'dataGridActions',
            columnsProvider: 'ns = ${ $.ns }, componentType = columns',
            imports: {
                addColumns: '${ $.columnsProvider }:elems'
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
            this.elems.each('applyState', '', 'visible');

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

            return elem.visible ?
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
