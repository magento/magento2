/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/registry/registry'
], function (ko, _, utils, registry) {
    'use strict';

    function extractData(owner, str) {
        var data = str.split(':');

        if (!data[1]) {
            data[1] = data[0];
            data[0] = owner.name;
        }

        return {
            target: data[0],
            property: data[1]
        };
    }

    function notEmpty(value) {
        return typeof value !== 'undefined' && value != null;
    }

    function update(owner, value) {
        var component = owner.component,
            property = owner.property;

        component.set(property, value);
    }

    function getValue(owner) {
        var component = owner.component,
            property = owner.property;

        return utils.nested(component, property);
    }

    function form(ownerComponent, targetComponent, ownerProp, targetProp, direction) {
        var result,
            tmp;

        result = {
            owner: {
                component: ownerComponent,
                property: ownerProp
            },
            target: {
                component: targetComponent,
                property: targetProp
            }
        };

        if (direction === 'exports') {
            tmp = result.owner;
            result.owner = result.target;
            result.target = tmp;
        }

        return result;
    }

    function setData(store, direction, property, data) {
        var maps;

        if (!store.maps) {
            store.maps = {};
        }

        maps = store.maps;

        maps[direction] = maps[direction] || {};

        if (!Array.isArray(maps[direction][property])) {
            maps[direction][property] = [];
        }

        maps[direction][property].push(data);
    }

    function getData(store, direction, property) {
        var data,
            maps = store.maps;

        if (maps[direction] && maps[direction][property]) {
            data = maps[direction][property][0];
        } else {
            direction = direction === 'imports' ? 'exports' : 'imports';

            if (maps[direction] && maps[direction][property]) {
                data = maps[direction][property][0];
            }
        }

        return data;
    }

    return {
        setListners: function (listeners) {
            var data;

            _.each(listeners, function (callbacks, sources) {
                sources = sources.split(' ');
                callbacks = callbacks.split(' ');

                sources.forEach(function (target) {
                    callbacks.forEach(function (callback) {
                        data = extractData(this, target);

                        setData(this, 'imports', callback, data);

                        this.link(callback, data, 'imports');
                    }, this);
                }, this);
            }, this);
        },

        setLinks: function (links, direction) {
            _.each(links, function (data, property) {
                data = extractData(this, data);

                this.observe(property);

                setData(this, direction, property, data);

                this.link(property, data, direction)
                    .transfer(direction, property, data);
            }, this);

            return this;
        },

        link: function (property, data, direction) {
            var owner,
                formated;

            registry.get(data.target, function (component) {
                var callback;

                formated = form(component, this, data.property, property, direction);
                owner = formated.owner;

                callback = update.bind(null, formated.target);

                owner.component.on(owner.property, callback);
            }.bind(this));

            return this;
        },

        transfer: function (direction, property, data) {
            var formated,
                value;

            data = data || getData(this, direction, property);

            if (!data) {
                return this;
            }

            registry.get(data.target, function (component) {
                formated = form(component, this, data.property, property, direction);
                value = getValue(formated.owner);

                if (!notEmpty(value)) {
                    return;
                }

                update(formated.target, value);
            }.bind(this));

            return this;
        },

        export: function (property, data) {
            return this.transfer('exports', property, data);
        },

        import: function (property, data) {
            return this.transfer('imports', property, data);
        }
    };
});
