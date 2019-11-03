/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    /**
     * @constructor
     */
    var ConditionsDataNormalizer = function () {
        this.patterns = {
            validate: /^[a-z0-9_.-][a-z0-9_.-]*(?:\[(?:\d*|[a-z0-9_.-]+)\])*$/i,
            key: /[a-z0-9_.-]+|(?=\[\])/gi,
            push: /^$/,
            fixed: /^\d+$/,
            named: /^[a-z0-9_.-]+$/i
        };
    };

    ConditionsDataNormalizer.prototype = {
        /**
         * Will convert an object:
         * {
         *   "foo[bar][1][baz]": 123,
         *   "foo[bar][1][blah]": 321
         *   "foo[bar][1--1][ah]": 456
         * }
         *
         * to
         * {
         *   "foo": {
         *     "bar": {
         *       "1": {
         *         "baz": 123,
         *         "blah": 321
         *       },
         *       "1--1": {
         *         "ah": 456
         *       }
         *     }
         *   }
         * }
         */
        normalize: function normalize(value) {
            var el, _this = this;

            this.pushes = {};
            this.data = {};

            _.each(value, function (e, i) {
                el = {};
                el[i] = e;

                _this._addPair({
                    name: i,
                    value: e
                });
            });

            return this.data;
        },

        /**
         * @param {Object} base
         * @param {String} key
         * @param {String} value
         * @return {Object}
         * @private
         */
        _build: function build(base, key, value) {
            base[key] = value;

            return base;
        },

        /**
         * @param {Object} root
         * @param {String} value
         * @return {*}
         * @private
         */
        _makeObject: function makeObject(root, value) {
            var keys = root.match(this.patterns.key),
                k, idx; // nest, nest, ..., nest

            while ((k = keys.pop()) !== undefined) {
                // foo[]
                if (this.patterns.push.test(k)) {
                    idx = this._incrementPush(root.replace(/\[\]$/, ''));
                    value = this._build([], idx, value);
                } // foo[n]
                else if (this.patterns.fixed.test(k)) {
                    value = this._build({}, k, value);
                } // foo; foo[bar]
                else if (this.patterns.named.test(k)) {
                    value = this._build({}, k, value);
                }
            }

            return value;
        },

        /**
         * @param {String} key
         * @return {Number}
         * @private
         */
        _incrementPush: function incrementPush(key) {
            if (this.pushes[key] === undefined) {
                this.pushes[key] = 0;
            }

            return this.pushes[key]++;
        },

        /**
         * @param {Object} pair
         * @return {Object}
         * @private
         */
        _addPair: function addPair(pair) {
            var obj = this._makeObject(pair.name, pair.value);

            if (!this.patterns.validate.test(pair.name)) {
                return this;
            }

            this.data = $.extend(true, this.data, obj);

            return this;
        }
    };

    return ConditionsDataNormalizer;
});
