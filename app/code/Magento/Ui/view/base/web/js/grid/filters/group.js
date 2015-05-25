/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/form/components/group'
], function (_, Group) {
    'use strict';

    return Group.extend({
        defaults: {
            template: 'ui/grid/filters/elements/group',
            isGroup: true
        },

        reset: function () {
            this.elems.each('reset');

            return this;
        },

        clear: function () {
            this.elems.each('clear');

            return this;
        },

        hasData: function () {
            return this.elems.some('hasData');
        },

        getPreview: function () {
            return this.elems.map('getPreview');
        }
    });
});
