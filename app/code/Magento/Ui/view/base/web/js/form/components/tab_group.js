/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            var source = this.source,
                result  = elem.delegate('validate'),
                invalid = false;

            _.some(result, function (item) {
                return !item.valid && (invalid = item.target);
            });

            if (invalid && !source.get('params.invalid')) {
                source.set('params.invalid', true);

                elem.activate();
                invalid.focused(true);
            }
        },

        /**
         * Sets 'allValid' property of instance to true, then calls 'validate' method
         * of instance for each element.
         */
        onValidate: function () {
            var elems;

            elems = this.elems.sortBy(function (elem) {
                return !elem.active();
            });

            elems.forEach(this.validate, this);
        }
    });
});
