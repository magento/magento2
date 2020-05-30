/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-shadow */

define([
    'jquery',
    'underscore',
    'mage/utils/objects',
    'mage/utils/strings'
], function ($, _, utils, stringUtils) {
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

    /**
     * Objects can specify how to use templating for their properties - getting that configuration.
     *
     * To disable rendering for all properties of your object add __disableTmpl: true.
     * To disable for specific property add __disableTmpl: {propertyName: true}.
     * To limit recursion for a specific property add __disableTmpl: {propertyName: numberOfCycles}.
     *
     * @param {String} tmpl
     * @param {Object | undefined} target
     * @returns {Boolean|Object}
     */
    function isTmplIgnored(tmpl, target) {
        var parsedTmpl;

        try {
            parsedTmpl = JSON.parse(tmpl);

            if (typeof parsedTmpl === 'object') {
                return tmpl.includes('__disableTmpl');
            }
        } catch (e) {
        }

        if (typeof target !== 'undefined') {
            if (typeof target === 'object' && target.hasOwnProperty('__disableTmpl')) {
                return target.__disableTmpl;
            }
        }

        return false;

    }

    if (hasStringTmpls) {

        /*eslint-disable no-unused-vars, no-eval*/
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

        /*eslint-enable no-unused-vars, no-eval*/
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

            tmpl = _.template(tmpl, {
                variable: '$'
            })(data);

            tmplSettings.interpolate = cached;

            return tmpl;
        };
    }

    /**
     * Checks if provided value contains template syntax.
     *
     * @param {*} value - Value to be checked.
     * @returns {Boolean}
     */
    function isTemplate(value) {
        return typeof value === 'string' &&
            value.indexOf(opener) !== -1 &&
            // the below pattern almost always indicates an accident which should not cause template evaluation
            // refuse to evaluate
            value.indexOf('${{') === -1;
    }

    /**
     * Iteratively processes provided string
     * until no templates syntax will be found.
     *
     * @param {String} tmpl - Template string.
     * @param {Object} data - Data object used in a template.
     * @param {Boolean} [castString=false] - Flag that indicates whether template
     *      should be casted after evaluation to a value of another type or
     *      that it should be leaved as a string.
     * @param {Number|undefined} maxCycles - Maximum number of rendering cycles, can be 0.
     * @returns {*} Compiled template.
     */
    function render(tmpl, data, castString, maxCycles) {
        var last = tmpl,
            cycles = 0;

        while (~tmpl.indexOf(opener) && (typeof maxCycles === 'undefined' || cycles < maxCycles)) {
            if (!isTmplIgnored(tmpl)) {
                tmpl = template(tmpl, data);
            }

            if (tmpl === last) {
                break;
            }

            last = tmpl;
            cycles++;
        }

        return castString ?
            stringUtils.castString(tmpl) :
            tmpl;
    }

    return {

        /**
         * Applies provided data to the template.
         *
         * @param {Object|String} tmpl
         * @param {Object} [data] - Data object to match with template.
         * @param {Boolean} [castString=false] - Flag that indicates whether template
         *      should be casted after evaluation to a value of another type or
         *      that it should be leaved as a string.
         * @returns {*}
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
         *              x1: 2, x2: 5,
         *              delta: '${ $.x2 - $.x1 }',
         *              baz: 'Upper ${ $.foo.toUpperCase() }'
         *      };
         *
         *      utils.template(tmpl, source);
         *      => {
         *          key: {'Some': 'Random Stuff'},
         *          foo: 'bar',
         *          x1: 2, x2: 5,
         *          delta: 3,
         *          baz: 'Upper BAR'
         *      };
         */
        template: function (tmpl, data, castString, dontClone) {
            if (typeof tmpl === 'string') {
                return render(tmpl, data, castString);
            }

            if (!dontClone) {
                tmpl = utils.copy(tmpl);
            }

            tmpl.$data = data || {};

            /**
             * Template iterator function.
             */
            _.each(tmpl, function iterate(value, key, list) {
                var disabled,
                    maxCycles;

                if (key === '$data') {
                    return;
                }

                if (isTemplate(key)) {
                    delete list[key];

                    key = render(key, tmpl);
                    list[key] = value;
                }

                if (isTemplate(value)) {
                    //Getting template disabling settings, can be true for all disabled and separate settings
                    //for each property.
                    disabled = isTmplIgnored(value, list);

                    if (typeof disabled === 'object' && disabled.hasOwnProperty(key) && disabled[key] !== false) {
                        //Checking if specific settings for a property provided.
                        maxCycles = disabled[key];
                    }

                    if (disabled === true || maxCycles === true) {
                        //Rendering for all properties is disabled.
                        maxCycles = 0;
                    }

                    list[key] = render(value, tmpl, castString, maxCycles);
                } else if ($.isPlainObject(value) || Array.isArray(value)) {
                    _.each(value, iterate);
                }
            });

            delete tmpl.$data;

            return tmpl;
        }
    };
});
