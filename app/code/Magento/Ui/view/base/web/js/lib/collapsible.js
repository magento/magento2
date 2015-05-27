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
            opened: false,
            collapsible: true
        },

        /**
         * Initializes 'opened' observable, calls 'initObservable' of parent
         *
         * @return {Object} - reference to instance
         */
        initObservable: function () {
            this._super()
                .observe('opened');

            return this;
        },

        /**
         * Toggles 'active' observable, triggers 'active' event
         *
         * @return {Object} - reference to instance
         */
        toggleOpened: function () {
            if (this.collapsible) {
                this.opened(!this.opened());
            }

            return this;
        },

        close: function () {
            if (this.collapsible) {
                this.opened(false);
            }

            return this;
        }
    });
});
