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
            opened: false
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Collapsible} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('opened');

            return this;
        },

        /**
         * Toggles value of the 'opened' property.
         *
         * @returns {Collapsible} Chainable.
         */
        toggleOpened: function () {
            this.opened(!this.opened());

            return this;
        }
    });
});
