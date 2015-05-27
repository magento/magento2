/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/utils/objects'
], function (_, utils) {
    'use strict';

    var tmplSettings = _.templateSettings,
        interpolate = /\$\{([\s\S]+?)\}/g,
        opener = '${',
        template,
        hasStringTmpls;

    /**
     * Identifies whether ES6 templates are supported.
     */
    hasStringTmpls = (function () {
        var testString = 'var foo = "bar"; return `${ foo }` === foo';

        try {
            return Function(testString)();
        } catch (e) {
            return false;
        }
    })();

    if (hasStringTmpls) {
        /**
         * Evaluates template string using ES6 templates.
         *
         * @param {String} tmpl - Template string.
         * @param {Object} $ - Data object used in a template.
         * @returns {String} Compiled template.
         */
        template = function (tmpl, $) {
            return eval('`' + tmpl + '`');
        };
    } else {
        /**
         * Fallback function used when ES6 templates are not supported.
         * Uses underscore templates renderer.
         *
         * @param {String} tmpl - Template string.
         * @param {Object} data - Data object used in a template.
         * @returns {String} Compiled template.
         */
        template = function (tmpl, data) {
            var cached = tmplSettings.interpolate;

            tmplSettings.interpolate = interpolate;

            tmpl = _.template(tmpl, {variable: '$'})(data);

            tmplSettings.interpolate = cached;

            return tmpl;
        };
    }

    /**
     * Checks if provided value contains template syntax.
     *
     * @param {*} value - Value to be check.
     * @returns {Boolean}
     */
    function isTemplate(value) {
        return typeof value === 'string' && ~value.indexOf(opener);
    }

    /**
     * Iteratively processes provided string
     * until no templates syntax will be found.
     *
     * @param {String} tmpl - Template string.
     * @param {Object} data - Data object used in a template.
     * @returns {String} Compiled template.
     */
    function render(tmpl, data) {
        var last = tmpl;

        data = Object.create(data);

        while (~tmpl.indexOf(opener)) {
            tmpl = template(tmpl, data);

            if (tmpl === last) {
                break;
            }

            last = tmpl;
        }

        return tmpl;
    }

    return {
        /**
         * Applies provided data to the template.
         *
         * @param {Object|String} tmpl
         * @param {Object} [data] - Data object to match with template.
         * @returns {Object|String}
         *
         * @example Template defined as a string.
         *      var source = { foo: 'Random Stuff', bar: 'Some' };
         *
         *      utils.template('${ $.bar } ${ $.foo }', source);
         *      => 'Some Random Stuff';
         *
         * @example Template defined as an object.
         *      var tmpl = {
         *              key: {'${ $.$data.bar }': '${ $.$data.foo }'},
         *              foo: 'bar',
         *              baz: 'Upper ${ $.foo.toUpperCase() }'
         *      };
         *
         *      utils.template(tmpl, source);
         *      => {
         *          key: {'Some': 'Random Stuff'},
         *          foo: 'bar',
         *          baz: 'Upper BAR'
         *      };
         */
        template: function (tmpl, data) {
            if (typeof tmpl === 'string') {
                return render(tmpl, data);
            }

            tmpl = utils.extend({}, tmpl);

            tmpl.$data = data || {};

            _.each(tmpl, function iterate(value, key, list) {
                if (key === '$data') {
                    return;
                }

                if (isTemplate(key)) {
                    delete list[key];

                    key = render(key, tmpl);
                    list[key] = value;
                }

                if (isTemplate(value)) {
                    list[key] = render(value, tmpl);
                } else if (_.isObject(value)) {
                    _.each(value, iterate);
                }
            });

            delete tmpl.$data;

            return tmpl;
        }
    };
});
