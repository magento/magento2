/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function (registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            parentOption: null
        },

        /**
         * Initialize component.
         *
         * @returns {Element}
         */
        initialize: function () {
            return this
                ._super()
                .initLinkToParent();
        },

        /**
         * Cache link to parent component - option holder.
         *
         * @returns {Element}
         */
        initLinkToParent: function () {
            var pathToParent = this.parentName.replace(/(\.[^.]*){2}$/, '');

            this.parentOption = registry.async(pathToParent);
            this.value() && this.parentOption('label', this.value());

            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.parentOption(function (component) {
                component.set('label', value ? value : component.get('headerLabel'));
            });

            return this._super();
        }
    });
});
