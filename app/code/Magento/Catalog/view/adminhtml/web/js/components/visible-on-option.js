/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiCollection'
], function (Group) {
    'use strict';

    return Group.extend({
        defaults: {
            valuesForOptions: [],
            visibilityState: true,
            imports: {
                toggleVisibility: '${ $.parentName }.frontend_input:value'
            }
        },

        initElement: function (item) {
            this._super();
            item.set('visible', this.visibilityState);
            return this;
        },

        toggleVisibility: function (selected) {
            var isShown = this.visibilityState = selected in this.valuesForOptions; //this.valuesForOptions.indexOf(selected) !== -1;

            this.elems.each(function (child) {
               child.set('visible', isShown);
            });
        }
    });
});
