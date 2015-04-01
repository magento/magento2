/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/collapsible'
], function (Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/columns',
            viewportSize: 18
        },

        reset: function () {
            this.delegate('resetVisible');
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
