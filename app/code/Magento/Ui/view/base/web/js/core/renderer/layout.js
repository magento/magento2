/**
 * Copyright © Magento, Inc. All rights reserved.
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
        layout = {},
        cachedConfig = {};

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

        registry.get(node.deps, function (deps) {
            node.provider = node.extendProvider ? deps && deps.name : node.provider;
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

    function run(nodes, parent, cached, merge) {
        if (_.isBoolean(merge) && merge) {
            layout.merge(nodes);

            return false;
        }

        if (cached) {
            cachedConfig[_.keys(nodes)[0]] = JSON.parse(JSON.stringify(nodes));
        }

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

            if (!registry.has(node.name)) {
                this.addChild(parent, node)
                    .manipulate(node)
                    .initComponent(node);
            }

            if (node) {
                run(node.children, node);
            }

            return this;
        },

        build: function (parent, node, name) {
            var defaults    = parent && parent.childDefaults || {},
                children    = node.children,
                type        = getNodeType(parent, node),
                dataScope   = getDataScope(parent, node),
                component,
                extendDeps  = true,
                nodeName;

            node.children = false;
            node.extendProvider = true;

            if (node.config && node.config.provider || node.provider) {
                node.extendProvider = false;
            }

            if (node.config && node.config.deps || node.deps) {
                extendDeps= false;
            }

            node = utils.extend({
            }, types.get(type), defaults, node);

            nodeName = getNodeName(parent, node, name);

            if (registry.has(nodeName)) {
                component = registry.get(nodeName);
                component.children = children;

                return component;
            }

            if (extendDeps && parent && parent.deps && type) {
                node.deps = parent.deps;
            }

            _.extend(node, node.config || {}, {
                index: node.name || name,
                name: nodeName,
                dataScope: dataScope,
                parentName: utils.getPart(nodeName, -2),
                parentScope: utils.getPart(dataScope, -2)
            });

            node.children = children;
            node.componentType = node.type;

            delete node.type;
            delete node.config;

            if (children) {
                node.initChildCount = _.size(children);
            }

            if (node.isTemplate) {
                node.isTemplate = false;

                templates.set(node.name, node);
                registry.get(node.parentName, function (parent) {
                    parent.childTemplate = node;
                });

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
        },

        merge: function (components) {
            var cachedKey = _.keys(components)[0],
                compared = utils.compare(cachedConfig[cachedKey], components),
                remove = this.filterComponents(this.getByProperty(compared.changes, 'type', 'remove'), true),
                update = this.getByProperty(compared.changes, 'type', 'update'),
                dataSources = this.getDataSources(components),
                names, index, name, component;

            _.each(dataSources, function (val, key) {
                name = key.replace(/\.children|\.config/g, '');
                component = registry.get(name);

                component.cacheData();
                component.updateConfig(
                    true,
                    this.getFullConfig(key, components),
                    this.getFullConfig(key, cachedConfig[cachedKey])
                );
            }, this);

            _.each(remove, function (val) {
                component = registry.get(val.path);

                if (component) {
                    component.cleanData().destroy();
                }
            });

            update = _.compact(_.filter(update, function (val) {
                return !_.isEqual(val.oldValue, val.value);
            }));

            _.each(update, function (val) {
                names = val.path.split('.');
                index = Math.max(_.lastIndexOf(names, 'config'), _.lastIndexOf(names, 'children') + 2);
                name = _.without(names.splice(0, index), 'children', 'config').join('.');
                component = registry.get(name);

                if (val.name === 'sortOrder' && component) {
                    registry.get(component.parentName).insertChild(component, val.value);
                } else if (component) {
                    component.updateConfig(
                        val.oldValue,
                        val.value,
                        val.path
                    );
                }
            }, this);

            run(components, undefined, true);
        },

        getDataSources: function (config, parentPath) {
            var dataSources = {},
                key, obj;

            for (key in config) {
                if (config.hasOwnProperty(key)) {
                    if (
                        key === 'type' &&
                        config[key] === 'dataSource' &&
                        config.hasOwnProperty('config')
                    ) {
                        dataSources[parentPath + '.config'] = config.config;
                    } else if (_.isObject(config[key])) {
                        obj = this.getDataSources(config[key], utils.fullPath(parentPath, key));

                        _.each(obj, function (value, path) {
                            dataSources[path] = value;
                        });
                    }
                }
            }

            return dataSources;
        },

        getFullConfig: function (path, config) {
            var index;

            path = path.split('.');
            index = _.lastIndexOf(path, 'config');

            if (!~index) {
                return false;
            }
            path = path.splice(0, index);

            _.each(path, function (val) {
                config = config[val];
            });

            return config.config;
        },

        getByProperty: function (data, prop, propValue) {
            return _.filter(data, function (value) {
                return value[prop] === propValue;
            });
        },

        filterComponents: function (data, splitPath, index, separator, keyName) {
            var result = [],
                names, length;

            index = -2;
            separator = '.' || separator;
            keyName = 'children' || keyName;

            _.each(data, function (val) {
                names = val.path.split(separator);
                length  = names.length;

                if (names[length + index] === keyName) {
                    val.path = splitPath ? _.without(names, keyName).join(separator) : val.path;
                    result.push(val);
                }
            });

            return result;
        }
    });

    return run;
});
