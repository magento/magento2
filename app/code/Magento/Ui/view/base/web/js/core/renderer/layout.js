/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'mageUtils',
    'uiRegistry',
    './types',
    '../../lib/logger/console-logger'
], function (_, $, utils, registry, types, consoleLogger) {
    'use strict';

    var templates = registry.create(),
        layout = {},
        cachedConfig = {};

    /**
     * Build name from parent name and node name
     *
     * @param {Object} parent
     * @param {Object} node
     * @param {String} [name]
     * @returns {String}
     */
    function getNodeName(parent, node, name) {
        var parentName = parent && parent.name;

        if (typeof name !== 'string') {
            name = node.name || name;
        }

        return utils.fullPath(parentName, name);
    }

    /**
     * Get node type from node or parent.
     *
     * @param {Object} parent
     * @param {Object} node
     * @returns {String}
     */
    function getNodeType(parent, node) {
        return node.type || parent && parent.childType;
    }

    /**
     * Get data scope based on parent data scope and node data scope.
     *
     * @param {Object} parent
     * @param {Object} node
     * @returns {String}
     */
    function getDataScope(parent, node) {
        var dataScope = node.dataScope,
            parentScope = parent && parent.dataScope;

        return !utils.isEmpty(parentScope) ?
            !utils.isEmpty(dataScope) ?
                parentScope + '.' + dataScope :
                parentScope :
            dataScope || '';
    }

    /**
     * Load node dependencies on other instances.
     *
     * @param {Object} node
     * @returns {jQueryPromise}
     */
    function loadDeps(node) {
        var loaded = $.Deferred(),
            loggerUtils = consoleLogger.utils;

        if (node.deps) {
            consoleLogger.utils.asyncLog(
                loaded,
                {
                    data: {
                        component: node.name,
                        deps: node.deps
                    },
                    messages: loggerUtils.createMessages(
                        'depsStartRequesting',
                        'depsFinishRequesting',
                        'depsLoadingFail'
                    )
                }
            );
        }

        registry.get(node.deps, function (deps) {
            node.provider = node.extendProvider ? deps && deps.name : node.provider;
            loaded.resolve(node);
        });

        return loaded.promise();
    }

    /**
     * Load node component file via requirejs.
     *
     * @param {Object} node
     * @returns {jQueryPromise}
     */
    function loadSource(node) {
        var loaded = $.Deferred(),
            source = node.component;

        consoleLogger.info('componentStartLoading', {
            component: node.component
        });

        require([source], function (constr) {
            consoleLogger.info('componentFinishLoading', {
                component: node.component
            });
            loaded.resolve(node, constr);
        }, function () {
            consoleLogger.error('componentLoadingFail', {
                component: node.component
            });
        });

        return loaded.promise();
    }

    /**
     * Create a new component instance and set it to the registry.
     *
     * @param {Object} node
     * @param {Function} Constr
     */
    function initComponent(node, Constr) {
        var component = new Constr(_.omit(node, 'children'));

        consoleLogger.info('componentStartInitialization', {
            component: node.component,
            componentName: node.name
        });

        registry.set(node.name, component);
    }

    /**
     * Application entry point.
     *
     * @param {Object} nodes
     * @param {Object} parent
     * @param {Boolean} cached
     * @param {Boolean} merge
     * @returns {Boolean|undefined}
     */
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
        /**
         * Determines if node ready to be added or process it.
         *
         * @param {Object} parent
         * @param {Object|String} node
         */
        iterator: function (parent, node) {
            var action = _.isString(node) ?
                this.addChild :
                this.process;

            action.apply(this, arguments);
        },

        /**
         * Prepare component.
         *
         * @param {Object} parent
         * @param {Object} node
         * @param {String} name
         * @returns {Object}
         */
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

        /**
         * Detailed processing of component config.
         *
         * @param {Object} parent
         * @param {Object} node
         * @param {String} name
         * @returns {Boolean|Object}
         */
        build: function (parent, node, name) {
            var defaults    = parent && parent.childDefaults || {},
                children    = this.filterDisabledChildren(node.children),
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
                extendDeps = false;
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
                registry.get(node.parentName, function (parentComp) {
                    parentComp.childTemplate = node;
                });

                return false;
            }

            if (node.componentDisabled === true) {
                return false;
            }

            return node;
        },

        /**
         * Filter out all disabled components.
         *
         * @param {Object} children
         * @returns {*}
         */
        filterDisabledChildren: function (children) {
            var cIds;

            //cleanup children config.componentDisabled = true
            if (children && typeof children === 'object') {
                cIds = Object.keys(children);

                if (cIds) {
                    _.each(cIds, function (cId) {
                        if (typeof children[cId] === 'object' &&
                            children[cId].hasOwnProperty('config') &&
                            typeof children[cId].config === 'object' &&
                            children[cId].config.hasOwnProperty('componentDisabled') &&
                            children[cId].config.componentDisabled === true) {
                            delete children[cId];
                        }
                    });
                }
            }

            return children;
        },

        /**
         * Init component.
         *
         * @param {Object} node
         * @returns {Object}
         */
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
        /**
         * Loading component marked as isTemplate.
         *
         * @param {Object} parent
         * @param {Object} node
         * @returns {Object}
         */
        waitTemplate: function (parent, node) {
            var args = _.toArray(arguments);

            templates.get(node.nodeTemplate, function () {
                this.applyTemplate.apply(this, args);
            }.bind(this));

            return this;
        },

        /**
         * Waiting for parent component and process provided component.
         *
         * @param {Object} node
         * @param {String} name
         * @returns {Object}
         */
        waitParent: function (node, name) {
            var process = this.process.bind(this);

            registry.get(node.parent, function (parent) {
                process(parent, node, name);
            });

            return this;
        },

        /**
         * Processing component marked as isTemplate.
         *
         * @param {Object} parent
         * @param {Object} node
         * @param {String} name
         */
        applyTemplate: function (parent, node, name) {
            var template = templates.get(node.nodeTemplate);

            node = utils.extend({}, template, node);

            delete node.nodeTemplate;

            this.process(parent, node, name);
        }
    });

    _.extend(layout, {
        /**
         * Determines inserting strategy.
         *
         * @param {Object} node
         * @returns {Object}
         */
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

        /**
         * Insert component to provide target and position.
         *
         * @param {Object|String} item
         * @param {Object} target
         * @param {Number} position
         * @returns {Object}
         */
        insert: function (item, target, position) {
            registry.get(target, function (container) {
                container.insertChild(item, position);
            });

            return this;
        },

        /**
         * Insert component into multiple targets.
         *
         * @param {Object} item
         * @param {Array} targets
         * @returns {Object}
         */
        insertTo: function (item, targets) {
            _.each(targets, function (info, target) {
                this.insert(item, target, info.position);
            }, this);

            return this;
        },

        /**
         * Add provided child to parent.
         *
         * @param {Object} parent
         * @param {Object|String} child
         * @returns {Object}
         */
        addChild: function (parent, child) {
            var name;

            if (parent && parent.component) {
                name = child.name || child;

                this.insert(name, parent.name, child.sortOrder);
            }

            return this;
        },

        /**
         * Merge components configuration with cached configuration.
         *
         * @param {Array} components
         */
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
                    component.destroy();
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

        /**
         * Recursive dataSource assignment.
         *
         * @param {Object} config
         * @param {String} parentPath
         * @returns {Object}
         */
        getDataSources: function (config, parentPath) {
            var dataSources = {},
                key, obj;

            /* eslint-disable no-loop-func, max-depth */
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

            /* eslint-enable no-loop-func, max-depth */

            return dataSources;
        },

        /**
         * Configuration getter.
         *
         * @param {String} path
         * @param {Object} config
         * @returns {Boolean|Object}
         */
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

        /**
         * Filter data by property and value.
         *
         * @param {Object} data
         * @param {String} prop
         * @param {*} propValue
         */
        getByProperty: function (data, prop, propValue) {
            return _.filter(data, function (value) {
                return value[prop] === propValue;
            });
        },

        /**
         * Filter components.
         *
         * @param {Array} data
         * @param {Boolean} splitPath
         * @param {Number} index
         * @param {String} separator
         * @param {String} keyName
         * @returns {Array}
         */
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
