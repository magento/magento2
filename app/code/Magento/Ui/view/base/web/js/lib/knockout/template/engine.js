/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    './observable_source',
    './renderer',
    '../../logger/console-logger'
], function (ko, _, Source, renderer, consoleLogger) {
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
     * @param  {HTMLElement} templateDocument - document
     * @param  {Object} options - options, passed to template binding
     * @param  {ko.bindingContext} bindingContext
     * @returns {TemplateSource} Object with methods 'nodes' and 'data'.
     */
    RemoteTemplateEngine.prototype.makeTemplateSource = function (template, templateDocument, options, bindingContext) {
        var source,
            templateId;

        if (typeof template === 'string') {
            templateId = createTemplateIdentifier(template);
            source = sources[templateId];

            if (!source) {
                source = new Source(template);
                source.requestedBy = bindingContext.$data.name;
                sources[templateId] = source;

                consoleLogger.info('templateStartLoading', {
                    template: templateId,
                    component: bindingContext.$data.name
                });

                renderer.render(template).done(function (rendered) {
                    consoleLogger.info('templateLoadedFromServer', {
                        template: templateId,
                        component: bindingContext.$data.name
                    });
                    source.nodes(rendered);
                }).fail(function () {
                    consoleLogger.error('templateLoadingFail', {
                        template: templateId,
                        component: bindingContext.$data.name
                    });
                });
            }

            if (source.requestedBy !== bindingContext.$data.name) {
                consoleLogger.info('templateLoadedFromCache', {
                    template: templateId,
                    component: bindingContext.$data.name
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
        var templateSource = this.makeTemplateSource(template, templateDocument, options, bindingContext);

        return this.renderTemplateSource(templateSource, bindingContext, options);
    };

    return new RemoteTemplateEngine;
});
