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
            template: 'ui/grid/filters/chips'
        },

        /**
         * Defines if some of components' children has available previews.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return this.elems().some(function (elem) {
                return !!elem.previews().length;
            });
        },

        /**
         * Calls clear method on all of its' children.
         *
         * @returns {Chips} Chainable.
         */
        clear: function () {
            this.elems.each('clear');

            return this;
        }
    });
});
