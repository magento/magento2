/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiCollection'
], function (_, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'ui/grid/filters/chips',
            stickyTmpl: 'ui/grid/sticky/chips'
        },

        /**
         * Defines if some of components' children has available previews.
         *
         * @returns {Boolean}
         */
        hasPreviews: function () {
            return this.elems().some(function (elem) {
                return !!elem.previews.length;
            });
        },

        /**
         * Calls clear method on all of its' children.
         *
         * @returns {Chips} Chainable.
         */
        clear: function () {
            _.invoke(this.elems(), 'clear');

            return this;
        }
    });
});
