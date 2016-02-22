/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/components/fieldset'
], function (Fieldset) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            valuesForOptions: [],
            visibilityState: true,
            imports: {
                toggleVisibility: '${ $.parentName }.base_fieldset.frontend_input:value'
            }
        },

        initElement: function (item) {
            this._super();
            item.set('visible', this.visibilityState);
            return this;
        },

        toggleVisibility: function (selected) {
            var isShown = this.visibilityState = selected in this.valuesForOptions;
            this.visible(isShown);
            this.opened(isShown);
            this.elems.each(function (child) {
               child.set('visible', isShown);
            });
        }
    });
});
