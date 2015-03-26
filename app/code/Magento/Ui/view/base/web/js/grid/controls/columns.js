/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/controls/columns',
            active: false
        },

        initObservable: function () {
            this._super()
                .observe('active');

            return this;
        },

        countVisible: function () {
            return this.elems().filter(function (elem) {
                return elem.visible();
            }).length;
        },

        togglePanel: function () {
            this.active(!this.active());
        },

        hidePanel: function () {
            this.active(false);
        }
    });
});
