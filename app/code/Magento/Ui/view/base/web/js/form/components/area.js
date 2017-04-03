/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    './tab'
], function (_, Tab) {
    'use strict';

    return Tab.extend({
        defaults: {
            uniqueNs:   'params.activeArea',
            template:   'ui/area',
            changed:    false,
            loading:    false
        },

        /**
         * Extends instance with defaults. Invokes parent initialize method.
         * Calls initListeners and pushParams methods.
         */
        initialize: function () {
            _.bindAll(this, 'onChildrenUpdate', 'onContentLoading');

            return this._super();
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         * @return {Object} - reference to instance
         */
        initObservable: function () {
            this._super()
                .observe('changed loading');

            return this;
        },

        /**
         * Calls parent's initElement method.
         * Assignes callbacks on various events of incoming element.
         * @param  {Object} elem
         * @return {Object} - reference to instance
         */
        initElement: function (elem) {
            this._super();

            elem.on({
                'update':   this.onChildrenUpdate,
                'loading':  this.onContentLoading
            });

            return this;
        },

        /**
         * Is being invoked on children update.
         * Sets changed property to one incoming.
         * Invokes setActive method if settings
         * contain makeVisible property set to true.
         *
         * @param  {Boolean} hasChanged
         */
        onChildrenUpdate: function (hasChanged) {
            if (!hasChanged) {
                hasChanged = _.some(this.delegate('hasChanged'));
            }

            this.changed(hasChanged);
        },

        /**
         * Callback that sets loading property to true.
         */
        onContentLoading: function (isLoading) {
            this.loading(isLoading);
        }
    });
});
