/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore'
], function (_) {
    'use strict';

    var mixin = {
        defaults: {
            imports: {
                toggleDisabled: '${ $.parentName }.custom_use_parent_settings:checked'
            },
            useParent: false,
            useDefaults: false
        },

        /**
         * Disable form input if settings for parent section is used
         * or default value is applied.
         *
         * @param {Boolean} isUseParent
         */
        toggleDisabled: function (isUseParent) {
            var disabled = this.useParent = isUseParent;

            if (!disabled && !_.isUndefined(this.service)) {
                disabled = !!this.isUseDefault();
            }

            this.saveUseDefaults();
            this.disabled(disabled);
        },

        /**
         * Stores original state of the field.
         */
        saveUseDefaults: function () {
            this.useDefaults = this.disabled();
        },

        /** @inheritdoc */
        setInitialValue: function () {
            this._super();
            this.isUseDefault(this.useDefaults);

            return this;
        },

        /** @inheritdoc */
        toggleUseDefault: function (state) {
            this._super();
            this.disabled(state || this.useParent);
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
