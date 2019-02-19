/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (_, Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            listens: {
                '${ $.provider }:data.validate': 'onValidate'
            },
            collapsible: false,
            opened: true
        },

        /**
         * Invokes initElement method of parent class, calls 'initActivation' method
         * passing element to it.
         * @param {Object} elem
         * @returns {Object} - reference to instance
         */
        initElement: function (elem) {
            this._super()
                .initActivation(elem);

            return this;
        },

        /**
         * Activates element if one is first or if one has 'active' propert
         * set to true.
         *
         * @param  {Object} elem
         * @returns {Object} - reference to instance
         */
        initActivation: function (elem) {
            var elems   = this.elems(),
                isFirst = !elems.indexOf(elem);

            if (isFirst || elem.active()) {
                elem.activate();
            }

            return this;
        },

        /**
         * Delegates 'validate' method on element, then reads 'invalid' property
         * of params storage, and if defined, activates element, sets
         * 'allValid' property of instance to false and sets invalid's
         * 'focused' property to true.
         *
         * @param {Object} elem
         */
        validate: function (elem) {
            var result  = elem.delegate('validate'),
                invalid;

            invalid = _.find(result, function (item) {
                return typeof item !== 'undefined' && !item.valid;
            });

            if (invalid) {
                elem.activate();
                invalid.target.focused(true);
            }

            return invalid;
        },

        /**
         * Sets 'allValid' property of instance to true, then calls 'validate' method
         * of instance for each element.
         */
        onValidate: function () {
            this.elems.sortBy(function (elem) {
                return !elem.active();
            }).some(this.validate, this);
        }
    });
});
