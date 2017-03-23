/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/group',
    'uiRegistry',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate',
    'underscore'
], function (Group, uiRegistry, validation, $t, _) {
    'use strict';

    return Group.extend({
        defaults: {
            visible: true,
            label: '',
            showLabel: true,
            required: false,
            template: 'ui/group/group',
            fieldTemplate: 'ui/form/field',
            breakLine: true,
            validateWholeGroup: false,
            additionalClasses: {}
        },

        /** @inheritdoc */
        initialize: function () {
            validation.addRule('validate-fpt-group', function (value) {
                if (value.indexOf('?') !== -1) {

                    return false;
                }

                return true;
            }, $t('You must set unique country-state combinations within the same fixed product tax'));

            this._super();
        },

        /**
         *
         * @private
         */
        _handleOptionsAvailability: function () {
            var parent,
                dup;

            dup = {};
            parent = uiRegistry.get(uiRegistry.get(this.parentName).parentName);
            _.each(parent.elems(), function (elem) {
                var country,
                    state,
                    val,
                    key;

                country = uiRegistry.get(elem.name + '.countryState.country');
                state = uiRegistry.get(elem.name + '.countryState.state');
                val = uiRegistry.get(elem.name + '.countryState.val');

                key = country.value() + (state.value() > 0 ? state.value() : 0);
                dup[key]++;

                if (!dup[key]) {
                    dup[key] = 1;
                    val.value('');
                } else {
                    dup[key]++;
                    val.value(country.value() + '?' + country.name);
                }
            });
        },

        /** @inheritdoc */
        initElement: function (elem) {
            var obj;

            obj = this;
            this._super();
            elem.on('value', function () {
                obj._handleOptionsAvailability();
            });

            return this;
        }
    });
});
