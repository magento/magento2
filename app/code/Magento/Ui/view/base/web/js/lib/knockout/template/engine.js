/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'ko',
    'underscore',
    './observable_source',
    './renderer',
    '../../logger/console-logger'
], function ($, ko, _, Source, renderer, consoleLogger) {
    'use strict';

    var RemoteTemplateEngine,
        NativeTemplateEngine = ko.nativeTemplateEngine,
        sources = {};

    /**
     * Remote template engine class. Is used to be able to load remote templates via knockout template binding.
     */
    RemoteTemplateEngine = function () {
        // Instance reference for closure.
        var engine = this,
        // Decorate the builtin Knockout "template" binding to track synchronous template renders.
        origUpdate = ko.bindingHandlers.template.update;

        /**
         * Counter to track the number of currently running render tasks (both synchronous and asynchronous).
         * @type {Number}
         * @private
         */
        this._rendersOutstanding = 0;

        /**
         * Use a jQuery object as an event bus (but any event emitter with on/off/emit methods could work)
         * @type {jQuery}
         * @private
         */
        this._events = $(this);

        /**
         * Rendered templates
         * @type {Object}
         * @private
         */
        this._templatesRendered = {};

        /*eslint-disable no-unused-vars*/
        /**
         * Decorate update method
         *
         * @param {HTMLElement} element
         * @param {Function} valueAccessor
         * @param {Object} allBindings
         * @param {Object} viewModel
         * @param {ko.bindingContext} bindingContext
         * @returns {*}
         */
        ko.bindingHandlers.template.update = function (element, valueAccessor, allBindings, viewModel, bindingContext) {
            /*eslint-enable no-unused-vars*/
            var options = ko.utils.peekObservable(valueAccessor()),
                templateName,
                isSync,
                updated;

            if (typeof options === 'object') {
                if (options.templateEngine && options.templateEngine !== engine) {
                    return origUpdate.apply(this, arguments);
                }

                if (!options.name) {
                    consoleLogger.error('Could not find template name', options);
                }
                templateName = options.name;
            } else if (typeof options === 'string') {
                templateName = options;
            } else {
                consoleLogger.error('Could not build a template binding', options);
            }
            engine._trackRender(templateName);
            isSync = engine._hasTemplateLoaded(templateName);
            updated = origUpdate.apply(this, arguments);

            if (isSync) {
                engine._releaseRender(templateName, 'sync');
            }

            return updated;
        };
    };

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
     * When an asynchronous render task begins, increment the internal counter for tracking when renders are complete.
     * @private
     */
    RemoteTemplateEngine.prototype._trackRender = function (templateName) {
        var rendersForTemplate = this._templatesRendered[templateName] !== undefined ?
            this._templatesRendered[templateName] : 0;

        this._rendersOutstanding++;
        this._templatesRendered[templateName] = rendersForTemplate + 1;
        this._resolveRenderWaits();
    };

    /**
     * When an asynchronous render task ends, decrement the internal counter for tracking when renders are complete.
     * @private
     */
    RemoteTemplateEngine.prototype._releaseRender = function (templateName) {
        var rendersForTemplate = this._templatesRendered[templateName];

        this._rendersOutstanding--;
        this._templatesRendered[templateName] = rendersForTemplate - 1;
        this._resolveRenderWaits();
    };

    /**
     * Check to see if renders are complete and trigger events for listeners.
     * @private
     */
    RemoteTemplateEngine.prototype._resolveRenderWaits = function () {
        if (this._rendersOutstanding === 0) {
            this._events.triggerHandler('finishrender');
        }
    };

    /**
     * Get a promise for the end of the current run of renders, both sync and async.
     * @return {jQueryPromise} - promise that resolves when render completes
     */
    RemoteTemplateEngine.prototype.waitForFinishRender = function () {
        var defer = $.Deferred();

        this._events.one('finishrender', defer.resolve);

        return defer.promise();
    };

    /**
     * Returns true if this template has already been asynchronously loaded and will be synchronously rendered.
     * @param {String} templateName
     * @returns {Boolean}
     * @private
     */
    RemoteTemplateEngine.prototype._hasTemplateLoaded = function (templateName) {
        // Sources object will have cached template once makeTemplateSource has run
        return sources.hasOwnProperty(templateName);
    };

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
        var engine = this,
            source,
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

                renderer.render(template).then(function (rendered) {
                    consoleLogger.info('templateLoadedFromServer', {
                        template: templateId,
                        component: bindingContext.$data.name
                    });
                    source.nodes(rendered);
                    engine._releaseRender(templateId, 'async');
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

        return this.renderTemplateSource(templateSource);
    };

    return new RemoteTemplateEngine;
});
