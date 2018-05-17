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
            }
        },

        /**
         * Disable form input if settings for parent section is used
         * or default value is applied.
         *
         * @param {Boolean} isUseParent
         */
        toggleDisabled: function (isUseParent) {
            var disabled = isUseParent;

            if (!disabled && !_.isUndefined(this.service)) {
                disabled = !!this.isUseDefault() || this.disabled();
            }

            this.disabled(disabled);
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
