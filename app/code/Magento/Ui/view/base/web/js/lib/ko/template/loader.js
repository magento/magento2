/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';

    /**
     * Formats path of type "path.to.template" to RequireJS compatible
     * @param  {String} path
     * @return {String} - formatted template path
     */
    function formatTemplatePath(path) {
        return 'text!' + path.replace(/^([^\/]+)/g, '$1/template') + '.html';
    }

    /**
     * Removes license from incoming template
     *
     * @param  {String} tmpl
     * @return {String} - template without license
     */
    function removeLicense(tmpl) {
        var regEx = /<!--[\s\S]*?-->/;

        return tmpl.replace(regEx, function (match) {
            return ~match.indexOf('/**') ? '' : match;
        });
    }

    /**
     * Loads template by path, resolves promise with it if defined
     *
     * @param  {String} path
     * @param  {Deferred} promise
     */
    function load(path, promise) {
        require([path], function (template) {
            template = removeLicense(template);

            promise.resolve(template);
        });
    }

    return {

        /**
         * Loops over arguments and loads template for each.
         * @return {Deferred} - promise of templates to be loaded
         */
        loadTemplate: function (source) {
            var isLoaded = $.Deferred();

            this._loadTemplate(source).done(function (tmpl) {
                isLoaded.resolve(tmpl);
            });

            return isLoaded.promise();
        },

        /**
         * Loads template by it's name
         *
         * @param  {String} name
         * @return {Deferred}
         */
        _loadTemplate: function (name) {
            var isLoaded = $.Deferred(),
                path = formatTemplatePath(name);

            load(path, isLoaded);

            return isLoaded.promise();
        }
    };
});
