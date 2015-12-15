/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            listens: {
                value: 'updateExternalValue',
                externalValue: 'updateValue'
            },

            links:{
                value: '${ $.provider }:${ $.dataScope }',
                externalValue: '${ $.externalProvider }:columnData'
            },

            valuesFormatter: null//must be plugged-in as a module
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            this.observe('value', 'externalValue');

            return this;
        },

        updateExternalValue: function (val) {
            if (this.valuesFormatter && this.valuesFormatter.updateExternalValue) {
                this.set('externalValue', valuesFormatter.updateExternalValue(val));
            }
            else {
                this.set('externalValue', val);
            }
        },

        updateValue: function (extVal) {
            if (this.valuesFormatter && this.valuesFormatter.updateValue) {
                this.set('value', valuesFormatter.updateValue(extVal));
            }
            else {
                this.set('value', extVal);
            }
        }
    });
});
