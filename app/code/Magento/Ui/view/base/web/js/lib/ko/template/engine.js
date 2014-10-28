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
    'ko',
    './observable_source',
    '../../renderer/renderer',
    'mage/apply/main'
], function (ko, Source, Renderer, Mage) {
    'use strict';

    var sources = {};

    /**
     * Creates unique template identifier based on template name and it's extenders (optional)
     * @param  {String} templateName
     * @param  {Object} templateOptions
     * @return {String} - unique template identifier
     */
    function createTemplateIdentifier(templateName, templateOptions) {
        var extenders = templateOptions.extenders || [];

        return templateName + '|' + extenders.join(' ');
    }

    /**
     * Remote template engine class. Is used to be able to load remote templates via knockout template binding.
     */
    var RemoteTemplateEngine = function() {};
    var NativeTemplateEngine = ko.nativeTemplateEngine;

    RemoteTemplateEngine.prototype = new NativeTemplateEngine;
    RemoteTemplateEngine.prototype.constructor = RemoteTemplateEngine;

    /**
     * Overrided method of native knockout template engine.
     * Caches template after it's unique name and renders in once.
     * If template name is not typeof string, delegates work to knockout.templateSources.anonymousTemplate.
     * @param  {*} template
     * @param  {HTMLElement} templateDocument - document
     * @param  {Object} options - options, passed to template binding
     * @return {TemplateSource} - object with methods 'nodes' and 'data'.
     */
    RemoteTemplateEngine.prototype.makeTemplateSource = function(template, templateDocument, options) {
        var source,
            extenders = options.extenders || [],
            templateId;

        if (typeof template === 'string') {
            templateId = createTemplateIdentifier(template, options);
            source = sources[templateId];

            if (!source) {
                source = new Source(template);
                sources[templateId] = source;

                Renderer.render(template, extenders).done(function(rendered) {
                    source.nodes(rendered);
                    Mage.apply();
                });
            }

            return source;

        } else if ((template.nodeType == 1) || (template.nodeType == 8)) {
            return new ko.templateSources.anonymousTemplate(template);
        } else {
            throw new Error("Unknown template type: " + template);
        }
    };

    /**
     * Overrided method of native knockout template engine.
     * Should return array of html elements.
     * @param  {TemplateSource} templateSource - object with methods 'nodes' and 'data'.
     * @param  {ko.bindingContext} bindingContext
     * @param  {Object} options - options, passed to template binding
     * @return {Array} - array of html elements
     */
    RemoteTemplateEngine.prototype.renderTemplateSource = function (templateSource, bindingContext, options) {
        var nodes = templateSource.nodes();

        return ko.utils.cloneNodes(nodes);
    };

    /**
     * Overrided method of native knockout template engine.
     * Created in order to invoke makeTemplateSource method with custom set of params.
     * @param  {*} template - template identifier
     * @param  {ko.bindingContext} bindingContext
     * @param  {Object} options - options, passed to template binding
     * @param  {HTMLElement} templateDocument - document
     * @return {Array} - array of html elements
     */
    RemoteTemplateEngine.prototype.renderTemplate = function (template, bindingContext, options, templateDocument) {
        var templateSource = this['makeTemplateSource'](template, templateDocument, options);
        return this['renderTemplateSource'](templateSource, bindingContext, options);
    };

    return new RemoteTemplateEngine;
});