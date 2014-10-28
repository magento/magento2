/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define(['jquery'], function($) {
    'use strict';
    
    var storage = window.localStorage;

    function getStoragePathFor(name, entity) {
        return '__' + entity + 'Cache__' + name;
    }

    /**
     * Converts arrayLikeObject to array
     * @param  {Object|Array} arrayLikeObject - target
     * @return {Array} - result array
     */
    function toArray(arrayLikeObject) {
        return Array.prototype.slice.call(arrayLikeObject);
    }

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

    return {
        /**
         * Loops over arguments and loads template for each.
         * @return {Deferred} - promise of templates to be loaded
         */
        loadTemplate: function() {
            var isLoaded  = $.Deferred(),
                templates = toArray(arguments);

            waitFor(templates.map(this._loadTemplate)).done(function () {
                templates = toArray(arguments);
                isLoaded.resolve.apply(isLoaded, templates);
            });

            return isLoaded.promise();
        },

        _loadTemplate: function (name) {
            var isLoaded    = $.Deferred(),
                storagePath = getStoragePathFor(name, 'template'),
                path        = formatTemplatePath(name),
                cached;

            if (allowLocalCache) {
                cached = storage.getItem(storagePath) || null;
            }

            if (cached) {
                setTimeout(function () {
                    isLoaded.resolve(cached);    
                }, 0)
            } else {
                require([path], function (template) {
                    storage.setItem(storagePath, template);
                    isLoaded.resolve(template);
                });
            }

            return isLoaded.promise();

        }
    }
});