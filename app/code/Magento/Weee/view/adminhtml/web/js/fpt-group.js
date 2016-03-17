/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/group',
    'uiRegistry',
    "Magento_Ui/js/lib/validation/validator",
    'mage/translate'
], function (Group, uiRegistry, validation, $t) {
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
        initialize: function() {
            var obj = this;
            validation.addRule('validate-fpt-group', function(value) {
                if(value.indexOf('?') !== -1) {
                    return false;
                }
                return true;
            }, $t('You must set unique country-state combinations within the same fixed product tax'), 'dsds', 'dsdassd');

            this._super();
        },
        /**
         *
         * @private
         */
        _handleOptionsAvailability: function () {
            var parent,
                dup,
                obj;
            obj = this;
            dup = {};
            parent = uiRegistry.get(uiRegistry.get(this.parentName).parentName);
            _.each(parent.elems(), function (elem) {
                var country,
                    state,
                    val,
                    website,
                    key;
                country = uiRegistry.get(elem.name + '.countryState.country');
                state = uiRegistry.get(elem.name + '.countryState.state');
                val = uiRegistry.get(elem.name + '.countryState.val');

                key = country.value() + ( state.value() > 0 ? state.value() : 0);
                dup[key]++;
                if (!dup[key]) {
                    dup[key] = 1;
                    val.value('');
                } else {
                    dup[key] = dup[key] + 1;
                    val.value(country.value() + '?' + country.name);
                }
            });
        },
        initElement: function (elem) {
            var country,
                splitArray,
                obj;
            obj = this;
            this._super();
            elem.on('value', function () {
                    obj._handleOptionsAvailability();
            });
            return this;
        }
    });
});
