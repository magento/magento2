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

        reset: function () {
            this.delegate('resetVisible');
        },

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

        hasOverflow: function () {
            return this.elems().length > this.viewportSize;
        },

        isDisabled: function (elem) {
            var count = this.countVisible(),
                isLast = elem.visible() && count === 1,
                isTooMuch = count > this.viewportMaxSize;

            return isLast || isTooMuch;
        },

        countVisible: function () {
            return this.elems().filter(function (elem) {
                return elem.visible();
            }).length;
        },

        getHeaderMessage: function () {
            return _.template(this.headerMessage, {
                visible: this.countVisible(),
                total: this.elems().length
            });
        }
    });
});
