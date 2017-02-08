/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    './loader'
], function ($, _, loader) {
    'use strict';

    var colonReg    = /\\:/g,
        attributes  = {},
        elements    = {},
        globals     = [],
        renderer,
        preset;

    renderer = {

        /**
         * Loads template by provided path and
         * than converts it's content to html.
         *
         * @param {String} tmplPath - Path to the template.
         * @returns {jQueryPromise}
         */
        render: function (tmplPath) {
            var loadPromise = loader.loadTemplate(tmplPath);

            return loadPromise.then(renderer.parseTemplate);
        },

        /**
         * Parses provided string as html content
         * and returns an array of DOM elements.
         *
         * @param {String} html - String to be processed.
         * @returns {Array}
         */
        parseTemplate: function (html) {
            var fragment = document.createDocumentFragment();

            $(fragment).append(html);

            return renderer.normalize(fragment);
        },

        /**
         * Processes custom attributes and nodes of provided DOM element.
         *
         * @param {HTMLElement} content - Element to be processed.
         * @returns {Array} An array of content's child nodes.
         */
        normalize: function (content) {
            globals.forEach(function (handler) {
                handler(content);
            });

            return _.toArray(content.childNodes);
        },

        /**
         * Adds new global content handler.
         *
         * @param {Function} handler - Function which will be invoked for
         *      an every content passed to 'normalize' method.
         * @returns {Renderer} Chainable.
         */
        addGlobal: function (handler) {
            if (!_.contains(globals, handler)) {
                globals.push(handler);
            }

            return this;
        },

        /**
         * Removes specified global content handler.
         *
         * @param {Function} handler - Handler to be removed.
         * @returns {Renderer} Chainable.
         */
        removeGlobal: function (handler) {
            var index = globals.indexOf(handler);

            if (~index) {
                globals.splice(index, 1);
            }

            return this;
        },

        /**
         * Adds new custom attribute handler.
         *
         * @param {String} id - Attribute identifier.
         * @param {(Object|Function)} [config={}]
         * @returns {Renderer} Chainable.
         */
        addAttribute: function (id, config) {
            var data = {
                name: id,
                binding: id,
                handler: renderer.handlers.attribute
            };

            if (_.isFunction(config)) {
                data.handler = config;
            } else if (_.isObject(config)) {
                _.extend(data, config);
            }

            data.id = id;
            attributes[id] = data;

            return this;
        },

        /**
         * Removes specified attribute handler.
         *
         * @param {String} id - Attribute identifier.
         * @returns {Renderer} Chainable.
         */
        removeAttribute: function (id) {
            delete attributes[id];

            return this;
        },

        /**
         * Adds new custom node handler.
         *
         * @param {String} id - Node identifier.
         * @param {(Object|Function)} [config={}]
         * @returns {Renderer} Chainable.
         */
        addNode: function (id, config) {
            var data = {
                name: id,
                binding: id,
                handler: renderer.handlers.node
            };

            if (_.isFunction(config)) {
                data.handler = config;
            } else if (_.isObject(config)) {
                _.extend(data, config);
            }

            data.id = id;
            elements[id] = data;

            return this;
        },

        /**
         * Removes specified custom node handler.
         *
         * @param {String} id - Node identifier.
         * @returns {Renderer} Chainable.
         */
        removeNode: function (id) {
            delete elements[id];

            return this;
        },

        /**
         * Checks if provided DOM element is a custom node.
         *
         * @param {HTMLElement} node - Node to be checked.
         * @returns {Boolean}
         */
        isCustomNode: function (node) {
            return _.some(elements, function (elem) {
                return elem.name.toUpperCase() === node.tagName;
            });
        },

        /**
         * Processes custom attributes of a content's child nodes.
         *
         * @param {HTMLElement} content - DOM element to be processed.
         */
        processAttributes: function (content) {
            var repeat;

            repeat = _.some(attributes, function (attr) {
                var attrName = attr.name,
                    nodes    = content.querySelectorAll('[' + attrName + ']'),
                    handler  = attr.handler;

                return _.toArray(nodes).some(function (node) {
                    var data = node.getAttribute(attrName);

                    return handler(node, data, attr) === true;
                });
            });

            if (repeat) {
                renderer.processAttributes(content);
            }
        },

        /**
         * Processes custom nodes of a provided content.
         *
         * @param {HTMLElement} content - DOM element to be processed.
         */
        processNodes: function (content) {
            var repeat;

            repeat = _.some(elements, function (element) {
                var nodes   = content.querySelectorAll(element.name),
                    handler = element.handler;

                return _.toArray(nodes).some(function (node) {
                    var data = node.getAttribute('args');

                    return handler(node, data, element) === true;
                });
            });

            if (repeat) {
                renderer.processNodes(content);
            }
        },

        /**
         * Wraps provided string in curly braces if it's necessary.
         *
         * @param {String} args - String to be wrapped.
         * @returns {String} Wrapped string.
         */
        wrapArgs: function (args) {
            if (~args.indexOf('\\:')) {
                args = args.replace(colonReg, ':');
            } else if (~args.indexOf(':') && !~args.indexOf('}')) {
                args = '{' + args + '}';
            }

            return args;
        },

        /**
         * Wraps child nodes of provided DOM element
         * with knockout's comment tag.
         *
         * @param {HTMLElement} node - Node whose children should be wrapped.
         * @param {String} binding - Name of the binding for the opener comment tag.
         * @param {String} data - Data associated with a binding.
         *
         * @example
         *      <div id="example"><span/></div>
         *      wrapChildren(document.getElementById('example'), 'foreach', 'data');
         *      =>
         *      <div id="example">
         *      <!-- ko foreach: data -->
         *          <span></span>
         *      <!-- /ko -->
         *      </div>
         */
        wrapChildren: function (node, binding, data) {
            var tag = this.createComment(binding, data),
                $node = $(node);

            $node.prepend(tag.open);
            $node.append(tag.close);
        },

        /**
         * Wraps specified node with knockout's comment tag.
         *
         * @param {HTMLElement} node - Node to be wrapped.
         * @param {String} binding - Name of the binding for the opener comment tag.
         * @param {String} data - Data associated with a binding.
         *
         * @example
         *      <div id="example"></div>
         *      wrapNode(document.getElementById('example'), 'foreach', 'data');
         *      =>
         *      <!-- ko foreach: data -->
         *          <div id="example"></div>
         *      <!-- /ko -->
         */
        wrapNode: function (node, binding, data) {
            var tag = this.createComment(binding, data),
                $node = $(node);

            $node.before(tag.open);
            $node.after(tag.close);
        },

        /**
         * Creates knockouts' comment tag for the provided binding.
         *
         * @param {String} binding - Name of the binding.
         * @param {String} data - Data associated with a binding.
         * @returns {Object} Object with an open and close comment elements.
         */
        createComment: function (binding, data) {
            return {
                open: document.createComment(' ko ' + binding + ': ' + data + ' '),
                close: document.createComment(' /ko ')
            };
        }
    };

    renderer.handlers = {

        /**
         * Basic node handler. Replaces custom nodes
         * with a corresponding knockout's comment tag.
         *
         * @param {HTMLElement} node - Node to be processed.
         * @param {String} data
         * @param {Object} element
         * @returns {Boolean} True
         *
         * @example Sample syntaxes conversions.
         *      <with args="model">
         *          <span/>
         *      </with>
         *      =>
         *      <!-- ko with: model-->
         *          <span/>
         *      <!-- /ko -->
         */
        node: function (node, data, element) {
            data = renderer.wrapArgs(data);

            renderer.wrapNode(node, element.binding, data);
            $(node).replaceWith(node.childNodes);

            return true;
        },

        /**
         * Base attribute handler. Replaces custom attributes with
         * a corresponding knockouts' data binding.
         *
         * @param {HTMLElement} node - Node to be processed.
         * @param {String} data - Data associated with a binding.
         * @param {Object} attr - Attribute definition.
         *
         * @example Sample syntaxes conversions.
         *      <div text="label"></div>
         *      =>
         *      <div data-bind="text: label"></div>
         */
        attribute: function (node, data, attr) {
            data = renderer.wrapArgs(data);

            renderer.bindings.add(node, attr.binding, data);
            node.removeAttribute(attr.name);
        },

        /**
         * Wraps provided node with a knockouts' comment tag.
         *
         * @param {HTMLElement} node - Node that will be wrapped.
         * @param {String} data - Data associated with a binding.
         * @param {Object} attr - Attribute definition.
         *
         * @example
         *      <div outereach="data" class="test"></div>
         *      =>
         *      <!-- ko foreach: data -->
         *          <div class="test"></div>
         *      <!-- /ko -->
         */
        wrapAttribute: function (node, data, attr) {
            data = renderer.wrapArgs(data);

            renderer.wrapNode(node, attr.binding, data);
            node.removeAttribute(attr.name);
        }
    };

    renderer.bindings = {

        /**
         * Appends binding string to the current
         * 'data-bind' attribute of provided node.
         *
         * @param {HTMLElement} node - DOM element whose 'data-bind' attribute will be extended.
         * @param {String} name - Name of a binding.
         * @param {String} data - Data associated with the binding.
         */
        add: function (node, name, data) {
            var bindings = this.get(node);

            if (bindings) {
                bindings += ', ';
            }

            bindings += name;

            if (data) {
                bindings += ': ' + data;
            }

            this.set(node, bindings);
        },

        /**
         * Extracts value of a 'data-bind' attribute from provided node.
         *
         * @param {HTMLElement} node - Node whose attribute to be extracted.
         * @returns {String}
         */
        get: function (node) {
            return node.getAttribute('data-bind') || '';
        },

        /**
         * Sets 'data-bind' attribute of the specified node
         * to the provided value.
         *
         * @param {HTMLElement} node - Node whose attribute will be altered.
         * @param {String} bindings - New value of 'data-bind' attribute.
         */
        set: function (node, bindings) {
            node.setAttribute('data-bind', bindings);
        }
    };

    renderer
        .addGlobal(renderer.processAttributes)
        .addGlobal(renderer.processNodes);

    /**
     * Collection of default binding conversions.
     */
    preset = {
        nodes: _.object([
            'if',
            'text',
            'with',
            'scope',
            'ifnot',
            'foreach',
            'component'
        ], Array.prototype),
        attributes: _.object([
            'css',
            'attr',
            'html',
            'with',
            'text',
            'click',
            'event',
            'submit',
            'enable',
            'disable',
            'options',
            'visible',
            'template',
            'hasFocus',
            'textInput',
            'component',
            'uniqueName',
            'optionsText',
            'optionsValue',
            'checkedValue',
            'selectedOptions'
        ], Array.prototype)
    };

    _.extend(preset.attributes, {
        if: renderer.handlers.wrapAttribute,
        ifnot: renderer.handlers.wrapAttribute,
        innerif: {
            binding: 'if'
        },
        innerifnot: {
            binding: 'ifnot'
        },
        outereach: {
            binding: 'foreach',
            handler: renderer.handlers.wrapAttribute
        },
        foreach: {
            name: 'each'
        },
        value: {
            name: 'ko-value'
        },
        style: {
            name: 'ko-style'
        },
        checked: {
            name: 'ko-checked'
        },
        disabled: {
            name: 'ko-disabled',
            binding: 'disable'
        },
        focused: {
            name: 'ko-focused',
            binding: 'hasFocus'
        },

        /**
         * Custom 'render' attrobute handler function. Wraps child elements
         * of a node with knockout's 'ko template:' comment tag.
         *
         * @param {HTMLElement} node - Element to be processed.
         * @param {String} data - Data specified in 'render' attribute of a node.
         */
        render: function (node, data) {
            data = data || 'getTemplate()';
            data = renderer.wrapArgs(data);

            renderer.wrapChildren(node, 'template', data);
            node.removeAttribute('render');
        }
    });

    _.extend(preset.nodes, {
        foreach: {
            name: 'each'
        },

        /**
         * Custom 'render' node handler function.
         * Replaces node with knockout's 'ko template:' comment tag.
         *
         * @param {HTMLElement} node - Element to be processed.
         * @param {String} data - Data specified in 'args' attribute of a node.
         */
        render: function (node, data) {
            data = data || 'getTemplate()';
            data = renderer.wrapArgs(data);

            renderer.wrapNode(node, 'template', data);
            $(node).replaceWith(node.childNodes);
        }
    });

    _.each(preset.attributes, function (data, id) {
        renderer.addAttribute(id, data);
    });

    _.each(preset.nodes, function (data, id) {
        renderer.addNode(id, data);
    });

    return renderer;
});
