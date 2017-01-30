/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'mageUtils',
    'uiRegistry',
    './types'
], function (_, $, utils, registry, types) {
    'use strict';

    var templates = registry.create(),
        layout = {};

    function getNodeName(parent, node, name) {
        var parentName = parent && parent.name;

        if (typeof name !== 'string') {
            name = node.name || name;
        }

        return utils.fullPath(parentName, name);
    }

    function getNodeType(parent, node) {
        return node.type || parent && parent.childType;
    }

    function getDataScope(parent, node) {
        var dataScope = node.dataScope,
            parentScope = parent && parent.dataScope;

        return !utils.isEmpty(parentScope) ?
            !utils.isEmpty(dataScope) ?
                parentScope + '.' + dataScope :
                parentScope :
            dataScope || '';
    }

    function loadDeps(node) {
        var loaded = $.Deferred();

        registry.get(node.deps, function () {
            loaded.resolve(node);
        });

        return loaded.promise();
    }

    function loadSource(node) {
        var loaded = $.Deferred(),
            source = node.component;

        require([source], function (constr) {
            loaded.resolve(node, constr);
        });

        return loaded.promise();
    }

    function initComponent(node, Constr) {
        var component = new Constr(_.omit(node, 'children'));

        registry.set(node.name, component);
    }

    function run(nodes, parent) {
        _.each(nodes || [], layout.iterator.bind(layout, parent));
    }

    _.extend(layout, {
        iterator: function (parent, node) {
            var action = _.isString(node) ?
                this.addChild :
                this.process;

            action.apply(this, arguments);
        },

        process: function (parent, node, name) {
            if (!parent && node.parent) {
                return this.waitParent(node, name);
            }

            if (node.nodeTemplate) {
                return this.waitTemplate.apply(this, arguments);
            }

            node = this.build.apply(this, arguments);

            if (node) {
                this.addChild(parent, node)
                    .manipulate(node)
                    .initComponent(node);

                run(node.children, node);
            }

            return this;
        },

        build: function (parent, node, name) {
            var defaults    = parent && parent.childDefaults || {},
                children    = node.children,
                type        = getNodeType(parent, node),
                dataScope   = getDataScope(parent, node),
                nodeName;

            node.children = false;

            node = utils.extend({
            }, types.get(type), defaults, node);

            nodeName = getNodeName(parent, node, name);

            _.extend(node, node.config || {}, {
                index: node.name || name,
                name: nodeName,
                dataScope: dataScope,
                parentName: utils.getPart(nodeName, -2),
                parentScope: utils.getPart(dataScope, -2)
            });

            node.children = children;

            delete node.type;
            delete node.config;

            if (children) {
                node.initChildCount = _.size(children);
            }

            if (node.isTemplate) {
                node.isTemplate = false;

                templates.set(node.name, node);

                return false;
            }

            if (node.componentDisabled === true) {
                return false;
            }

            return node;
        },

        initComponent: function (node) {
            if (!node.component) {
                return this;
            }

            loadDeps(node)
                .then(loadSource)
                .done(initComponent);

            return this;
        }
    });

    _.extend(layout, {
        waitTemplate: function (parent, node) {
            var args = _.toArray(arguments);

            templates.get(node.nodeTemplate, function () {
                this.applyTemplate.apply(this, args);
            }.bind(this));

            return this;
        },

        waitParent: function (node, name) {
            var process = this.process.bind(this);

            registry.get(node.parent, function (parent) {
                process(parent, node, name);
            });

            return this;
        },

        applyTemplate: function (parent, node, name) {
            var template = templates.get(node.nodeTemplate);

            node = utils.extend({}, template, node);

            delete node.nodeTemplate;

            this.process(parent, node, name);
        }
    });

    _.extend(layout, {
        manipulate: function (node) {
            var name = node.name;

            if (node.appendTo) {
                this.insert(name, node.appendTo, -1);
            }

            if (node.prependTo) {
                this.insert(name, node.prependTo, 0);
            }

            if (node.insertTo) {
                this.insertTo(name, node.insertTo);
            }

            return this;
        },

        insert: function (item, target, position) {
            registry.get(target, function (container) {
                container.insertChild(item, position);
            });

            return this;
        },

        insertTo: function (item, targets) {
            _.each(targets, function (info, target) {
                this.insert(item, target, info.position);
            }, this);

            return this;
        },

        addChild: function (parent, child) {
            var name;

            if (parent && parent.component) {
                name = child.name || child;

                this.insert(name, parent.name, child.sortOrder);
            }

            return this;
        }
    });

    return run;
});
