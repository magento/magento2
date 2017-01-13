/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'uiRegistry'
], function (ko, _, utils, registry) {
    'use strict';

    function parseData(placeholder, data, direction) {
        if (typeof data !== 'string') {
            return false;
        }

        data = data.split(':');

        if (!data[0]) {
            return false;
        }

        if (!data[1]) {
            data[1] = data[0];
            data[0] = placeholder;
        }

        return {
            target: data[0],
            property: data[1],
            direction: direction
        };
    }

    function notEmpty(value) {
        return typeof value !== 'undefined' && value != null;
    }

    function updateValue(data, owner, target, value) {
        var component = target.component,
            property = target.property,
            linked = data.linked;

        if (data.mute) {
            return;
        }

        if (linked) {
            linked.mute = true;
        }

        if (owner.component !== target.component) {
            value = data.inversionValue ? !utils.copy(value) : utils.copy(value);
        }

        component.set(property, value, owner);

        if (linked) {
            linked.mute = false;
        }
    }

    function getValue(owner) {
        var component = owner.component,
            property = owner.property;

        return component.get(property);
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

    function setLinked(map, data) {
        var match;

        if (!map) {
            return;
        }

        match = _.findWhere(map, {
            linked: false,
            target: data.target,
            property: data.property
        });

        if (match) {
            match.linked = data;
            data.linked = match;
        }
    }

    function setData(maps, property, data) {
        var direction   = data.direction,
            map         = maps[direction];

        data.linked = false;

        (map[property] = map[property] || []).push(data);

        direction = direction === 'imports' ? 'exports' : 'imports';

        setLinked(maps[direction][property], data);
    }

    function setLink(target, owner, data, property, immediate) {
        var direction = data.direction,
            formated = form(target, owner, data.property, property, direction),
            callback,
            value;

        owner = formated.owner;
        target = formated.target;

        callback = updateValue.bind(null, data, owner, target);

        owner.component.on(owner.property, callback, target.component.name);

        if (immediate) {
            value = getValue(owner);

            if (notEmpty(value)) {
                updateValue(data, owner, target, value);
            }
        }
    }

    function transfer(owner, data) {
        var args = _.toArray(arguments);

        if (data.target.substr(0,1) === '!') {
            data.target = data.target.substr(1);
            data.inversionValue = true;
        }

        if (owner.name === data.target) {
            args.unshift(owner);

            setLink.apply(null, args);
        } else {
            registry.get(data.target, function (target) {
                args.unshift(target);

                setLink.apply(null, args);
            });
        }
    }

    return {
        setListeners: function (listeners) {
            var owner = this,
                data;

            _.each(listeners, function (callbacks, sources) {
                sources = sources.split(' ');
                callbacks = callbacks.split(' ');

                sources.forEach(function (target) {
                    callbacks.forEach(function (callback) {
                        data = parseData(owner.name, target, 'imports');

                        if (data) {
                            setData(owner.maps, callback, data);
                            transfer(owner, data, callback);
                        }
                    });
                });
            });

            return this;
        },

        setLinks: function (links, direction) {
            var owner = this,
                property,
                data;

            for (property in links) {
                if (links.hasOwnProperty(property)) {
                    data = parseData(owner.name, links[property], direction);

                    if (data) {
                        setData(owner.maps, property, data);
                        transfer(owner, data, property, true);
                    }
                }
            }

            return this;
        }
    };
});
