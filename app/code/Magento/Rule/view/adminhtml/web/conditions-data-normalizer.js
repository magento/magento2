/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    var ConditionsDataNormalizer = new Class.create();

    ConditionsDataNormalizer.prototype = {
        initialize: function () {
            this.patterns = {
                validate: /^[a-z0-9_-][a-z0-9_-]*(?:\[(?:\d*|[a-z0-9_-]+)\])*$/i,
                key: /[a-z0-9_-]+|(?=\[\])/gi,
                push: /^$/,
                fixed: /^\d+$/,
                named: /^[a-z0-9_-]+$/i
            };
        },

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
         *
         *
         */
        normalize: function normalize(value) {
            var _this = this;

            this.pushes = {};
            this.data = {};

            _.each(value, function (e, i) {
                var el = {};
                el[i] = e;

                _this._addPair({
                    name: i,
                    value: e
                });
            });

            return this.data;
        },

        _build: function build(base, key, value) {
            base[key] = value;

            return base;
        },

        _makeObject: function makeObject(root, value) {
            var keys = root.match(this.patterns.key),
                k; // nest, nest, ..., nest

            while ((k = keys.pop()) !== undefined) {
                // foo[]
                if (this.patterns.push.test(k)) {
                    var idx = this._incrementPush(root.replace(/\[\]$/, ''));
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

        _incrementPush: function incrementPush(key) {
            if (this.pushes[key] === undefined) {
                this.pushes[key] = 0;
            }

            return this.pushes[key]++;
        },

        _addPair: function addPair(pair) {
            if (!this.patterns.validate.test(pair.name)) return this;
            var obj = this._makeObject(pair.name, pair.value);
            this.data = $.extend(true, this.data, obj);
            return this;
        }
    };

    return ConditionsDataNormalizer;
});
