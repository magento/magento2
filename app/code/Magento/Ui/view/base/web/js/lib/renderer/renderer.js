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
define([
    '../loader',
    './overrides',
    'jquery',
    'underscore'
], function(loader, overrides, $, _) {
    'use strict';

    return {

        /**
         * Renders template and it's extenders using this._parse function.
         * Loads all extenders then merges them and wraps into div[data-template-extend="parent"] where parent is target template.
         * If no extenders provider, simply loads target template and passes execution to _parse.
         * @param {String} template - string, representing path to core template and it's extenders.
         * @param {Array} extenders - array of strings
         * @return {Deferred} - Promise of template to be rendered. Is being resolved with array of HTML elements.
         */
        render: function (template, extenders) {
            var isRendered = $.Deferred(),
                parent = template,

                extenders = extenders || [],
                extendersToLoad = [],
                extendersHtml = '',

                resolve       = isRendered.resolve.bind(isRendered),
                loadTemplate  = this._load.bind(this),
                parseTemplate = this._parse.bind(this);

            if (extenders.length) {

                loadTemplate.apply(this, extenders).done(function () {

                    toArray(arguments).forEach(function (chunk) {
                        extendersHtml += chunk;
                    });

                    extendersHtml = '<div data-template-extend="' + parent+ '">' + extendersHtml + '</div>';

                    parseTemplate(extendersHtml).done(resolve);

                }); 
            } else {

                loadTemplate(parent)
                    .then(parseTemplate)
                    .done(resolve);
            }

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
        _parse: function(rawHtml) {
            var templatePath,
                templateContainer,
                extendNodes,
                templatesToRender = [],
                extendPointsToRender = [];

            templateContainer = document.createDocumentFragment();

            wrap(toArray($(rawHtml)), templateContainer);

            extendNodes          = getExtendNodesFrom(templateContainer);
            templatesToRender    = extendNodes.map(extractTemplatePath, this)
            extendPointsToRender = templatesToRender.map(this.render, this);

            return waitFor(extendPointsToRender).then(function() {
                var correspondingExtendNode,
                    container,
                    newParts = [],
                    args = toArray(arguments);

                args.forEach(function(renderedNodes, idx) {
                    container = document.createDocumentFragment();
                    wrap(renderedNodes, container);

                    correspondingExtendNode = extendNodes[idx];
                    newParts = this._buildPartsMapFrom(correspondingExtendNode);

                    $(correspondingExtendNode).empty();

                    this._overridePartsOf(container)
                        .by(newParts)
                        .appendTo(correspondingExtendNode);

                }, this);

                return toArray(templateContainer.childNodes);
            }.bind(this));
        },

        /**
         * Builds parst map from HTML element by looking for all available override actions selectors.
         * @param  {HTMLElement} container - container to look up for new parts declarations
         * @return {Object} - Map of parts to apply. E.g. { toolbar: { replace: [HTMLElement1, HTMLElement2], append: [HTMLElement3] } }
         */
        _buildPartsMapFrom: function(container) {
            var partsMap = {},
                actionNodes,
                partSelector,
                targetPart,
                actions = overrides.getActions();

            actions.forEach(function(action) {
                partSelector = createActionSelectorFor(action);
                actionNodes  = toArray(container.querySelectorAll(partSelector));

                actionNodes.forEach(function(node) {
                    targetPart = node.getAttribute('data-part-' + action);

                    if (!partsMap[targetPart]) {
                        partsMap[targetPart] = {};
                    }

                    targetPart = partsMap[targetPart];

                    if (!targetPart[action]) {
                        targetPart[action] = [];
                    }

                    targetPart[action].push(node);
                });
            });

            return partsMap;
        },

        /**
         * Caches template and returns object for the sake of chaining
         * @param  {HTMLElement} template - container to look for parts to be overrided by new ones.
         * @return {Object}
         */
        _overridePartsOf: function(template) {
            return {

                /**
                 * Loops over newParts map and invokes override actions for each found.
                 * @param  {Object} newParts - the result of _buildPartsMapFrom method.
                 * @return {Object} - Returns object for the sake of chaining
                 */
                by: function(newParts) {
                    var oldElement;

                    _.each(newParts, function(actions, partName) {
                        _.each(actions, function(newElements, action) {

                            oldElement = template.querySelector(createPartSelectorFor(partName));
                            overrides[action](
                                oldElement,
                                newElements
                            );

                        });
                    });

                    return {

                        /**
                         * Appends template's (overrided already) children to extendNode.
                         * @param  {HTMLElement} extendNode - initial container of new parts declarations
                         */
                        appendTo: function(extendNode) {
                            if (template.hasChildNodes()) {
                                toArray(template.childNodes).forEach(function (child) {
                                    extendNode.appendChild(child);
                                });
                            }
                        }
                    }
                }
            }
        }
    };

    /**
     * Extracts template path from node by [data-part-extend] attribute
     * @param  {HTMLElement} node - node to look up for [data-part-extend] attr
     * @return {String} - value of [data-part-extend] attribute
     */
    function extractTemplatePath(node) {
        return node.getAttribute('data-template-extend');
    }

    /**
     * Looks up for [data-template-extend] selector in container.
     * @param  {HTMLElement} container - node to lookup
     * @return {Array} - array of found HTML elements
     */
    function getExtendNodesFrom(container) {
        return toArray(container.querySelectorAll('[data-template-extend]'))
    }

    /**
     * Checks if passed object has keys.
     * @param  {Object}  object - target object
     * @return {Boolean} - true, if object has no keys
     */
    function isEmpty(object) {
        return !Object.keys(object).length;
    }

    /**
     * Wraps nodes into container
     * @param  {Array} nodes - array of nodes
     * @param  {HTMLElement} container - target container
     */
    function wrap(nodes, container) {
        nodes.forEach(function (node) {
            container.appendChild(node);
        });
    }

    /**
     * Creates action selector.
     * @param  {String} action
     * @return {String} - Action selector
     */
    function createActionSelectorFor(action) {
        return '[data-part-' + action + ']';
    }

    /**
     * Creates data-part selector.
     * @param  {String} part
     * @return {String} - Part selector
     */
    function createPartSelectorFor(part) {
        return '[data-part="' + part + '"]';
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
     * Waits for all items in passed array of promises to resolve.
     * @param  {Array} promises - array of promises
     * @return {Deferred} - promise of promises to resolve
     */
    function waitFor(promises) {
        return $.when.apply(this, promises);
    }
});