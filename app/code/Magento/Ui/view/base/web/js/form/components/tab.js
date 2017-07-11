/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'uiCollection'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            uniqueProp:     'active',
            active:         false,
            wasActivated:   false
        },

        /**
         * Extends instance with defaults. Invokes parent initialize method.
         * Calls initListeners and pushParams methods.
         */
        initialize: function () {
            this._super()
                .setUnique();
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         * @return {Object} - reference to instance
         */
        initObservable: function () {
            this._super()
                .observe('active wasActivated');

            return this;
        },

        /**
         * Sets active property to true, then invokes pushParams method.
         */
        activate: function () {
            this.active(true);
            this.wasActivated(true);

            this.setUnique();

            return true;
        }
    });
});
