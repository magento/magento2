/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'uiRegistry'
], function (ko, _, utils, registry) {
    'use strict';

    /**
     * Parse provided data.
     *
     * @param {String} placeholder
     * @param {String} data
     * @param {String} direction
     * @returns {Boolean|Object}
     */
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

    /**
     * Check if value not empty.
     *
     * @param {*} value
     * @returns {Boolean}
     */
    function notEmpty(value) {
        return typeof value !== 'undefined' && value != null;
    }

    /**
     * Update value for linked component.
     *
     * @param {Object} data
     * @param {Object} owner
     * @param {Object} target
     * @param {*} value
     */
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

        if (property === 'disabled' && value) {
            component.set('validate', value, owner);
        }

        if (linked) {
            linked.mute = false;
        }
    }

    /**
     * Get value form owner component property.
     *
     * @param {Object} owner
     * @returns {*}
     */
    function getValue(owner) {
        var component = owner.component,
            property = owner.property;

        return component.get(property);
    }

    /**
     * Format provided params to object.
     *
     * @param {String} ownerComponent
     * @param {String} targetComponent
     * @param {String} ownerProp
     * @param {String} targetProp
     * @param {String} direction
     * @returns {Object}
     */
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

    /**
     * Set data to linked property.
     *
     * @param {Object} map
     * @param {Object} data
     */
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

    /**
     * Set data by direction.
     *
     * @param {Object} maps
     * @param {String} property
     * @param {Object} data
     */
    function setData(maps, property, data) {
        var direction   = data.direction,
            map         = maps[direction];

        data.linked = false;

        (map[property] = map[property] || []).push(data);

        direction = direction === 'imports' ? 'exports' : 'imports';

        setLinked(maps[direction][property], data);
    }

    /**
     * Set links for components.
     *
     * @param {String} target
     * @param {String} owner
     * @param {Object} data
     * @param {String} property
     * @param {Boolean} immediate
     */
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

    /**
     * Transfer data between components.
     *
     * @param {Object} owner
     * @param {Object} data
     */
    function transfer(owner, data) {
        var args = _.toArray(arguments);

        if (data.target.substr(0, 1) === '!') {
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
        /**
         * Assign listeners.
         *
         * @param {Object} listeners
         * @returns {Object} Chainable
         */
        setListeners: function (listeners) {
            var owner = this,
                data;

            _.each(listeners, function (callbacks, sources) {
                sources = sources.split(' ');
                callbacks = callbacks.split(' ');

                sources.forEach(function (target) {
                    callbacks.forEach(function (callback) {//eslint-disable-line max-nested-callbacks
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

        /**
         * Set links in provided direction.
         *
         * @param {Object} links
         * @param {String} direction
         * @returns {Object} Chainable
         */
        setLinks: function (links, direction) {
            var owner = this,
                property,
                data;

            for (property in links) {
                if (links.hasOwnProperty(property)) {
                    data = parseData(owner.name, links[property], direction);

                    if (data) {//eslint-disable-line max-depth
                        setData(owner.maps, property, data);
                        transfer(owner, data, property, true);
                    }
                }
            }

            return this;
        }
    };
});
