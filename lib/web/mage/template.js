/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'underscore'
        ], factory);
    } else {
        root.mageTemplate = factory(root._);
    }
}(this, function (_) {
    'use strict';

    /**
     * Checks if provided string is a valid DOM selector.
     *
     * @param {String} selector - Selector to be checked.
     * @returns {Boolean}
     */
    function isSelector(selector) {
        try {
            document.querySelector(selector);

            return true;
        } catch (e) {
            return false;
        }
    }

    /**
     * Unescapes characters used in underscore templates.
     *
     * @param {String} str - String to be processed.
     * @returns {String}
     */
    function unescape(str) {
        return str.replace(/&lt;%|%3C%/g, '<%').replace(/%&gt;|%%3E/g, '%>');
    }

    /**
     * If 'tmpl' is a valid selector, returns target node's innerHTML if found.
     * Else, returns empty string and emits console warning.
     * If 'tmpl' is not a selector, returns 'tmpl' as is.
     *
     * @param {String} tmpl
     * @returns {String}
     */
    function getTmplString(tmpl) {
        if (isSelector(tmpl)) {
            tmpl = document.querySelector(tmpl);

            if (tmpl) {
                tmpl = tmpl.innerHTML.trim();
            } else {
                console.warn('No template was found by selector: ' + tmpl);

                tmpl = '';
            }
        }

        return unescape(tmpl);
    }

    /**
     * Compiles or renders template provided either
     * by selector or by the template string.
     *
     * @param {String} tmpl - Template string or selector.
     * @param {(Object|Array|Function)} [data] - Data object with which to render template.
     * @returns {String|Function}
     */
    return function (tmpl, data) {
        var render;

        tmpl   = getTmplString(tmpl);
        render = _.template(tmpl);

        return !_.isUndefined(data) ?
            render(data) :
            render;
    };
}));
