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

    function extractData(placeholder, data, direction) {
        data = data.split(':');

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

    function update(data, owner, value) {
        var component = owner.component,
            property = owner.property,
            linked = data.linked;

        if (data.mute) {
            return;
        }

        if (linked) {
            linked.mute = true;
        }

        component.set(property, value);

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
        var direction = data.direction,
            map = maps[direction];

        data.linked = false;

        (map[property] = map[property] || []).push(data);

        direction = direction === 'imports' ? 'exports' : 'imports';

        setLinked(maps[direction][property], data);
    }

    function transfer(owner, property, data, type) {
        var direction = data.direction;

        registry.get(data.target, function (target) {
            var formated = form(target, owner, data.property, property, direction),
                callback,
                value;

            owner = formated.owner;
            target = formated.target;

            if (type === 'link' || type === 'both') {
                callback = update.bind(null, data, target);

                owner.component.on(owner.property, callback, target.component.name);
            }

            if (type === 'transfer' || type === 'both') {
                value = getValue(owner);

                if (notEmpty(value)) {
                    update(data, target, value);
                }
            }
        });
    }

    return {
        defaults: {
            maps: {
                exports: {},
                imports: {}
            }
        },

        setListners: function (listeners) {
            var owner = this,
                data;

            _.each(listeners, function (callbacks, sources) {
                sources = sources.split(' ');
                callbacks = callbacks.split(' ');

                sources.forEach(function (target) {
                    callbacks.forEach(function (callback) {
                        data = extractData(owner.name, target, 'imports');

                        setData(owner.maps, callback, data);
                        transfer(owner, callback, data, 'link');
                    });
                });
            });

            return this;
        },

        setLinks: function (links, direction) {
            var property,
                data;

            for (property in links) {
                data = extractData(this.name, links[property], direction);

                setData(this.maps, property, data);
                transfer(this, property, data, 'both');
            }

            return this;
        }
    };
});
