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
            valuesForOptions: [], //  ['select', 'multiselect'],
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
            //this.visible(this.valuesForOptions.indexOf(selected) != -1);
            //console.log(selected);
            //console.log(this.valuesForOptions);
            var isShown = this.visibilityState = this.valuesForOptions.indexOf(selected) !== -1;

            this.elems.each(function (child) {
               child.set('visible', isShown);
            });
        }
    });
});
