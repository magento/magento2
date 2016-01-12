/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    '../../form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({

        defaults: {
            listens: {
                'value': 'initValue'
            },

            links: {
                'text': 'value'
            }
        },

        /**
         * Extends instance with defaults, extends config with formatted values
         *     and options, and invokes initialize method of AbstractElement class.
         *     If instance's 'customEntry' property is set to true, calls 'initInput'
         */
        initialize: function () {
            this._super()
                .initValue();

            return this;
        },

        /**
         * Calls 'initObservable' of parent, initializes 'options' and 'initialOptions'
         *     properties, calls 'setOptions' passing options to it
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe(['text']);

            return this;
        },

        /**
         * Set value to text node
         */
        initValue: function () {
            this.value() ? this.text(this.value()) : this.value(this.text());
        }
    });
});
