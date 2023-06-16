/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/tabs'
], function ($, tabs) {
    'use strict';

    $.widget('mage.accordion', tabs, {
        options: {
            active: [0],
            multipleCollapsible: false,
            openOnFocus: false
        },

        /**
         * @private
         */
        _callCollapsible: function () {
            var self = this,
                disabled = false,
                active = false;

            if (typeof this.options.active === 'string') {
                this.options.active = this.options.active.split(' ').map(function (item) {
                    return parseInt(item, 10);
                });
            }

            $.each(this.collapsibles, function (i) {
                disabled = active = false;

                if ($.inArray(i, self.options.disabled) !== -1) {
                    disabled = true;
                }

                if ($.inArray(i, self.options.active) !== -1) {
                    active = true;
                }
                self._instantiateCollapsible(this, i, active, disabled);
            });
        },

        /**
         * Overwrites default functionality to provide the option to activate/deactivate multiple sections simultaneous
         * @param {*} action
         * @param {*} index
         * @private
         */
        _toggleActivate: function (action, index) {
            var self = this;

            if (Array.isArray(index && this.options.multipleCollapsible)) {
                $.each(index, function () {
                    self.collapsibles.eq(this).collapsible(action);
                });
            } else if (index === undefined && this.options.multipleCollapsible) {
                this.collapsibles.collapsible(action);
            } else {
                this._super(action, index);
            }
        },

        /**
         * If the Accordion allows multiple section to be active at the same time, if deep linking is used
         * sections that don't contain the id from anchor shouldn't be closed, otherwise the accordion uses the
         * tabs behavior
         * @private
         */
        _handleDeepLinking: function () {
            if (!this.options.multipleCollapsible) {
                this._super();
            }
        },

        /**
         * Prevent default behavior that closes the other sections when one gets activated if the Accordion allows
         * multiple sections simultaneous
         * @private
         */
        _closeOthers: function () {
            var self = this;

            if (!this.options.multipleCollapsible) {
                $.each(this.collapsibles, function () {
                    $(this).on('beforeOpen', function () {
                        self.collapsibles.not(this).collapsible('deactivate');
                    });
                });
            }
            $.each(this.collapsibles, function () {
                $(this).on('beforeOpen', function () {
                    var section = $(this);

                    section.addClass('allow').prevAll().addClass('allow');
                    section.nextAll().removeClass('allow');
                });
            });
        }
    });

    return $.mage.accordion;
});
