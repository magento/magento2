/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    'Magento_Ui/js/lib/collapsible'
], function (utils, Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/columns',
            viewportSize: 18
        },

        reset: function () {
            this.delegate('resetVisible');
        },

        apply: function () {
            var data = {},
                current;

            current = this.source.get('config.columns') || {};

            this.elems().forEach(function (elem) {
                data[elem.index] = {
                    visible: elem.visible()
                };
            });

            utils.extend(current, data);

            this.source.store('config.columns', current);
            this.close();
        },

        cancel: function () {
            var previous = this.source.get('config.columns'),
                config;

            this.elems().forEach(function (elem) {
                config = previous[elem.index] || {};

                elem.visible(config.visible);
            });
        },

        hasOverflow: function () {
            return this.elems().length > this.viewportSize;
        },

        isLastVisible: function (elem) {
            var visible = this.countVisible();

            return elem.visible() && visible === 1;
        },

        countVisible: function () {
            return this.elems().filter(function (elem) {
                return elem.visible();
            }).length;
        }
    });
});
