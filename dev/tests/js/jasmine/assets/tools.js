/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    return {
        /**
         * Processes configuration for a testsuite.
         *
         * @param {(Object|String)} config - Suite configuration.
         * @param {Object} tmplMap - Template map for test cases.
         */
        init: function (config, tmplMap) {
            var preset;

            if (_.isString(config)) {
                preset = JSON.parse(config);
            }

            this.applyBase(preset);

            if (tmplMap) {
                this.applyTmpls(preset, tmplMap);
            }

            return preset;
        },

        /**
         * Extends first levell properties of provided object
         * with a default configuration.
         *
         * @param {Object} data - Object to be modified.
         */
        applyBase: function (data) {
            var base = data.base = data.base || {};

            _.each(data, function (item) {
                _.defaults(item, base);
            });
        },

        /**
         * Renderes template based on template map and a source data.
         *
         * @param {Object} source - Data for a lookup.
         * @param {Object} map - Template map.
         */
        applyTmpls: function (source, map) {
            _.each(map, function (tmpl, suite) {
                suite = source[suite];

                suite.tmpl = _.template(tmpl)(suite);
            });
        },

        /**
         * Removes element by provided id.
         *
         * @param {String} id - Id of the element.
         */
        removeContainer: function (id) {
            var node = document.getElementById(id);

            if (node) {
                node.parentNode.removeChild(node);
            }
        }
    };
});
