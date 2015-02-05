/**
 * Copyright © 2015 Magento. All rights reserved.
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
        return str.replace(/&lt;%/g, '<%').replace(/%&gt;/g, '%>');
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
        if (isSelector(tmpl)) {
            tmpl = document.querySelector(tmpl).innerHTML;
        }

        tmpl = _.template(unescape(tmpl));

        return !_.isUndefined(data) ?
            tmpl(data) :
            tmpl;
    };
}));
