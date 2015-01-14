/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery'
], function(_, $) {
    'use strict';
    
    var storage = window.localStorage;

    /**
     * Formats path of type "path.to.template" to RequireJS compatible
     * @param  {String} path
     * @return {String} - formatted template path
     */
    function formatTemplatePath(path) {
        return 'text!' + path.replace(/^([^\/]+)/g, '$1/template') + '.html';
    }

    /**
     * Waits for all items in passed array of promises to resolve.
     * @param  {Array} promises - array of promises
     * @return {Deferred} - promise of promises to resolve
     */
    function waitFor(promises) {
        return $.when.apply(this, promises);
    }

    /**
     * Removes license from incoming template
     * 
     * @param  {String} tmpl
     * @return {String} - template without license
     */
    function removeLicense(tmpl){
        var regEx = /<!--[\s\S]*?-->/;

        return tmpl.replace(regEx, function(match){
            return ~match.indexOf('/**') ? '' : match;
        });
    }

    /**
     * Loads template by path, resolves promise with it if defined
     * 
     * @param  {String} path
     * @param  {Deferred} promise
     */
    function load(path, promise){
        require([path], function (template) {
            template = removeLicense(template);

            storage.setItem(path, template);

            if (promise) {
                promise.resolve(template);
            }
        });
    }

    return {
        /**
         * Loops over arguments and loads template for each.
         * @return {Deferred} - promise of templates to be loaded
         */
        loadTemplate: function() {
            var isLoaded    = $.Deferred(),
                templates   = _.toArray(arguments);

            waitFor(templates.map(this._loadTemplate)).done(function () {
                isLoaded.resolve.apply(isLoaded, arguments);
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
            var isLoaded    = $.Deferred(),
                path        = formatTemplatePath(name),
                cached      = storage.getItem(path);

            if (cached) {
                isLoaded.resolve(cached);
                load(path);
            } else {
                load(path, isLoaded);
            }

            return isLoaded.promise();
        }
    }
});