/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
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
     * Compiles or renders template provided either
     * by selector or by the template string.
     *
     * @param {String} tmpl - Template string or selector.
     * @param {(Object|Array)} [data] - Data object with which to render template.
     * @returns {String|Function}
     */
    return function (tmpl, data) {
        if (isSelector(tmpl)) {
            tmpl = document.querySelector(tmpl).innerHTML;
        }

        tmpl = _.template(tmpl);

        return typeof data === 'object' ?
            tmpl(data) :
            tmpl;
    };
});
