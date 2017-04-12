/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'underscore'
], function (Abstract, _) {
    'use strict';

    return Abstract.extend({
        defaults: {
            'mappingValues': {
                '1': true,
                '2': false
            },
            'checked': false,
            'mappedValue': '',
            'links': {
                value: false,
                'mappedValue': '${ $.provider }:${ $.dataScope }'
            },
            imports: {
                checked: 'mappedValue'
            }
        },

        /**
         * @returns {*}
         */
        setMappedValue: function () {
            var newValue;

            _.some(this.mappingValues, function (item, key) {
                if (item === this.value()) {
                    newValue = key;

                    return true;
                }
            }, this);

            return newValue;
        },

        /**
         * @returns {*}
         */
        initObservable: function () {
            return this.observe('mappedValue checked')._super();
        },

        /**
         * @returns {*}
         */
        setInitialValue: function () {
            this.value(this.mappedValue());
            this._super();
            this.mappedValue(this.initialValue);
            this.value(this.mappingValues[this.initialValue]);
            this.initialValue = this.value();

            return this;
        },

        /**
         * @returns {*}
         */
        onUpdate: function () {
            this.mappedValue(this.setMappedValue());

            return this._super();
        }
    });
});
