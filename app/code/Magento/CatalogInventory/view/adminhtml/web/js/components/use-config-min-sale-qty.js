/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'underscore',
    'uiRegistry'
], function (checkbox, _, registry) {
    'use strict';

    return checkbox.extend({
        defaults: {
            valueFromConfig: ''
        },

        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this
                ._super()
                .observe(['valueFromConfig']);
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            this.onCheckedChanged(this.checked());

            return this;
        },

        /**
         * @inheritdoc
         */
        'onCheckedChanged': function (newChecked) {
            var valueFromConfig = this.valueFromConfig();

            if (newChecked) {
                if (_.isArray(valueFromConfig)) {
                    this.changeVisibleDisabled(this.inputField, true, true, 1);
                } else if (_.isObject(valueFromConfig)) {
                    this.changeVisibleDisabled(this.inputField, false, true, null);
                    this.changeVisibleDisabled(this.dynamicRowsField, true, true, null);
                }
            } else {
                this.changeVisibleDisabled(this.inputField, true, false, null);
                this.changeVisibleDisabled(this.dynamicRowsField, false, true, null);
            }

            this._super(newChecked);
        },

        /**
         * Change visible and disabled
         *
         * @param {String} filter
         * @param {Boolean} visible
         * @param {Boolean} disabled
         * @param {Null|Number} valueFromConfig
         */
        changeVisibleDisabled: function (filter, visible, disabled, valueFromConfig) {
            registry.async(filter)(
                function (currentComponent) {
                    var initialValue = currentComponent.initialValue;

                    if (_.isString(initialValue) || valueFromConfig === 1) {
                        currentComponent.value(1);
                    } else if (initialValue) {
                        currentComponent.value(initialValue);
                    }

                    currentComponent.visible(visible);
                    currentComponent.disabled(disabled);
                }
            );
        }
    });
});
