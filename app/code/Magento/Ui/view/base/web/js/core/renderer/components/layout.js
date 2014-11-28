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
    'underscore',
    'jquery',
    'mage/utils',
    'Magento_Ui/js/lib/class',
    'Magento_Ui/js/lib/registry/registry'
], function(_, $, utils, Class, registry) {
    'use strict';

    function getNodeName(parent, node, name) {
        var parentName = parent && parent.name;

        if (typeof name !== 'string') {
            name = node.name || name;
        }

        if (parentName) {
            name = parentName + '.' + name;
        }

        return name;
    }

    function getNodeType(parent, node){
        return node.type || (parent && parent.childType);
    }

    function getDataScope(parent, node){
        var dataScope   = node.dataScope,
            parentScope = parent && parent.dataScope;

        return notEmpty(parentScope) ?
                ( notEmpty(dataScope) ?
                    (parentScope + '.' + dataScope) :
                    parentScope ) :
                (dataScope || '');
    }

    function notEmpty(value){
        return !_.isUndefined(value) && value !== '';
    }

    function mergeNode(node, config){
        return $.extend(true, {}, config, node);
    }

    function additional(node){
        return _.pick(node, 'name', 'index', 'dataScope');
    }

    function Layout(nodes, types){
        this.types      = types;
        this.registry   = registry.create();

        this.run(nodes);
    }

    _.extend(Layout.prototype, {
        run: function(nodes, parent){
            _.each(nodes || [], this.iterator.bind(this, parent));

            return this;
        },

        iterator: function(parent, node, name){
            var action = _.isString(node) ?
                this.addChild :
                this.process;

            action.apply(this, arguments);
        },

        process: function(parent, node, name) {
            if(!parent && node.parent){
                return this.waitParent(node, name);
            }

            if(node.template){
                return this.waitTemplate.apply(this, arguments);      
            }

            node = this.build.apply(this, arguments);

            if(node){
                this.addChild(parent, node.name)
                    .manipulate(node)
                    .initComponent(node)
                    .run(node.children, node);
            }

            return this;
        },

        build: function(parent, node, name){
            var type;

            type = getNodeType.apply(null, arguments);
            node = mergeNode(node, this.types.get(type));

            node.index      = node.name || name;
            node.name       = getNodeName(parent, node, name);
            node.dataScope  = getDataScope(parent, node);

            delete node.type;

            this.registry.set(node.name, node);

            return node.isTemplate ?
                (node.isTemplate = false) :
                node;
        },

        initComponent: function(node){
            var source = node.component,
                component;

            if(source){

                registry.get(node.deps, function(){

                    require([source], function(constr){

                        registry.set(node.name, new constr(
                            node.config,
                            additional(node)
                        ));
                    });
                });
            }

            return this;
        }
    });
        
    _.extend(Layout.prototype, {
        waitTemplate: function(parent, node, name){
            var args = _.toArray(arguments);

            this.registry.get(node.template, function(){
                this.applyTemplate.apply(this, args);
            }.bind(this));

            return this;
        },

        waitParent: function(node, name){
            var process = this.process.bind(this);

            this.registry.get(node.parent, function(parent){
                process(parent, node, name);
            });

            return this;
        },

        applyTemplate: function(parent, node, name){
            var template = this.registry.get(node.template);
            
            node = mergeNode(node, template);

            delete node.template;

            this.process(parent, node, name);
        }
    });

    _.extend(Layout.prototype, {
        manipulate: function(node) {
            var name = node.name;

            if (node.appendTo) {
                this.insert(name, node.appendTo, -1);
            }

            if (node.prependTo) {
                this.insert(name, node.prependTo, 0);
            }

            if(node.insertTo){
                this.insertTo(name, node.insertTo);
            }

            return this;
        },

        insert: function(item, target, position){
            registry.get(target, function(target){            
                target.insert(item, position);
            });

            return this;
        },

        insertTo: function(item, targets){
            _.each(targets, function(info, target){
                this.insert(item, target, info.position);
            }, this);

            return this;
        },

        addChild: function(parent, child){
            if(parent && parent.component){
                this.insert(child, parent.name);
            }

            return this;
        },

        clear: function(name){
            this.registry.remove(name);
        }
    });

    return Layout;
});