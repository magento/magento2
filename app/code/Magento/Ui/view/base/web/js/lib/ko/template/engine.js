/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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