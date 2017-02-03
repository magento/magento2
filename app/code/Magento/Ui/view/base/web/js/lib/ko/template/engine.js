/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    './observable_source',
    './renderer'
], function (ko, Source, Renderer) {
    'use strict';

    var RemoteTemplateEngine,
        NativeTemplateEngine = ko.nativeTemplateEngine,
        sources = {};

    /**
     * Remote template engine class. Is used to be able to load remote templates via knockout template binding.
     */
    RemoteTemplateEngine = function () {};

    /**
     * Creates unique template identifier based on template name and it's extenders (optional)
     * @param  {String} templateName
     * @return {String} - unique template identifier
     */
    function createTemplateIdentifier(templateName) {
        return templateName;
    }

    RemoteTemplateEngine.prototype = new NativeTemplateEngine;
    RemoteTemplateEngine.prototype.constructor = RemoteTemplateEngine;

    /**
     * Overrided method of native knockout template engine.
     * Caches template after it's unique name and renders in once.
     * If template name is not typeof string, delegates work to knockout.templateSources.anonymousTemplate.
     * @param  {*} template
     * @returns {TemplateSource} Object with methods 'nodes' and 'data'.
     */
    RemoteTemplateEngine.prototype.makeTemplateSource = function (template) {
        var source,
            templateId;

        if (typeof template === 'string') {
            templateId = createTemplateIdentifier(template);
            source = sources[templateId];

            if (!source) {
                source = new Source(template);
                sources[templateId] = source;

                Renderer.render(template).done(function (rendered) {
                    source.nodes(rendered);
                });
            }

            return source;
        } else if (template.nodeType === 1 || template.nodeType === 8) {
            source = new ko.templateSources.anonymousTemplate(template);

            return source;
        }

        throw new Error('Unknown template type: ' + template);
    };

    /**
     * Overrided method of native knockout template engine.
     * Should return array of html elements.
     * @param  {TemplateSource} templateSource - object with methods 'nodes' and 'data'.
     * @return {Array} - array of html elements
     */
    RemoteTemplateEngine.prototype.renderTemplateSource = function (templateSource) {
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
        var templateSource = this.makeTemplateSource(template, templateDocument, options);

        return this.renderTemplateSource(templateSource, bindingContext, options);
    };

    return new RemoteTemplateEngine;
});
