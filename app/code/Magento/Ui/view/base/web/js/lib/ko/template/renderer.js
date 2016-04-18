/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './loader',
    'jquery',
    'underscore'
], function (loader, $, _) {
    'use strict';

    return {

        /**
         * Renders template and it's extenders using this._parse function.
         * Loads all extenders then merges them and wraps into
         * div[data-template-extend="parent"] where parent is target template.
         * If no extenders provider, simply loads target template and passes execution to _parse.
         *
         * @param {String} template - string, representing path to core template and it's extenders.
         * @returns {Deferred} - Promise of template to be rendered. Is being resolved with array of HTML elements.
         */
        render: function (template) {
            var isRendered = $.Deferred(),
                resolve       = isRendered.resolve.bind(isRendered),
                loadTemplate  = this._load.bind(this),
                parseTemplate = this._parse.bind(this);

            loadTemplate(template)
                .then(parseTemplate)
                .done(resolve);

            return isRendered.promise();
        },

        /**
         * Loads templates via loader module.
         * @return {Deferred} - Promise of templates to be loaded
         */
        _load: function () {
            return loader.loadTemplate.apply(loader, arguments);
        },

        /**
         * Takes raw text (html), parses it, puts it to docuemntFragment container.
         * Looks up for all [data-template-exted] attributes, creates array of extend nodes.
         * Maps this array to extractTemplatePath to have all extend points pathes gathered.
         * Maps pathes to this.render method (which returns promise) and waits for this array to resolve.
         * Then looks up for [data-part-*] attributes and creates map of new parts.
         * Then overrides parent template's corresponding parts with new parts.
         * @param  {String} rawHtml - loaded raw text (html)
         * @return {Deferred} - Promise of template to be parsed. Is being resolved with array of HTML elements.
         */
        _parse: function (rawHtml) {
            return _.toArray($.parseHTML(rawHtml));
        }
    };
});
