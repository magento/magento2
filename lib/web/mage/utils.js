/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
//TODO: assemble all util methods in this module
define([
    'underscore'
], function (_) {
    'use strict';

    var tplRegxp = /\{(\w*)\}/g;

    /** @namespace */
    var utils = {};

    /**
     * Replaces matches of '{*}' pattern with a matched property in 'data' object.
     * @private
     *
     * @param {String} tpl - String to process.
     * @param {Object} data - Data to match with pattern.
     * @returns {String} Modified string.
     *
     * @example
     *      template('Hello {one}!', {one: 'World'});
     *      => 'Hello World!';
     */
    function template(tpl, data){
        return tpl.replace(tplRegxp, function(match, key){
            return data.hasOwnProperty(key) ? data[key] : '';
        });
    }

    /**
     * Sets nested property of a specified object.
     * @private
     *
     * @param {Object} parent - Object to look inside for the properties.
     * @param {Array} path - Splitted path the property.
     * @param {*} value - Value of the last property in 'path' array.
     * returns {*} New value for the property.
     */
    function setNested(parent, path, value){
        var last = path.pop();

        path.forEach(function(part) {
            if (_.isUndefined(parent[part])) {
                parent[part] = {};
            }

            parent = parent[part];
        });

        return (parent[last] = value);
    }

    /**
     * Retrieves value of a nested property.
     * @private
     *
     * @param {Object} parent - Object to look inside for the properties.
     * @param {Array} path - Splitted path the property.
     * @returns {*} Value of the property.
     */
    function getNested(parent, path){
        var exists;

        exists = path.every(function(part) {
            parent = parent[part];

            return !_.isUndefined(parent);
        });

        if(exists){
            return parent;
        }
    }

    /**
     * Removes property from a specified object.
     * @private
     *
     * @param {Object} parent - Object from which to remove property. 
     * @param {Array} path - Splitted path to the propery.
     */
    function removeNested(parent, path) {
        var field = path.pop();

        parent = getNested(parent, path);

        if (_.isObject(parent)) {
            delete parent[field];
        }
    }

    /**
     * Object manipulation methods.
     */
    _.extend(utils, {
        /**
         * Retrieves or defines objects' property by a composite path.
         *
         * @param {Object} data - Container for the properties specified in path.
         * @param {String} path - Objects' properties divided by dots.
         * @param {*} [value] - New value for the last property.
         * @returns {*} Returns value of the last property in chain.
         *
         * @example
         *      utils.nested({}, 'one.two', 3);
         *      => { one: {two: 3} }
         */
        nested: function(data, path, value){
            var action = arguments.length > 2 ? setNested : getNested;

            path = path ? path.split('.') : [];

            return action(data, path, value);
        },

        /**
         * Removes nested property from an object.
         *
         * @param {Object} data - Data source.
         * @param {String} path - Path to the property e.g. 'one.two.three'
         */
        nestedRemove: function(data, path) {
            path = path.split('.');

            removeNested(data, path);
        },

        /**
         * Flattens objects' nested properties.
         *
         * @param {Object} data - Object to flatten.
         * @param {String} [separator='.'] - Objects' keys separator.
         * @returns {Object} Flattened object.
         *
         * @example Example with a default separator.
         *      utils.flatten({one: { two: { three: 'value'} }});
         *      => { 'one.two.three': 'value' };
         *
         * @example Example with a custom separator.
         *      utils.flatten({one: { two: { three: 'value'} }}, '=>');
         *      => {'one=>two=>three': 'value'};
         */
        flatten: function(data, separator, parent, result){
            separator   = separator || '.';
            result      = result || {};

            _.each(data, function(node, name){
                if(parent){
                    name = parent + separator + name;
                }

                typeof node === 'object' ?
                    this.flatten(node, separator, name, result) :
                    (result[name] = node);

            }, this);

            return result;
        },

        /**
         * Opposite operation of the 'flatten' method.
         *
         * @param {Object} data - Previously flattened object.
         * @param {String} [separator='.'] - Keys separator.
         * @returns {Object} Object with nested properties.
         *
         * @example Example using custom separator.
         *      utils.unflatten({'one=>two': 'value'}, '=>');
         *      => {
         *          one: { two: 'value' }
         *      };      
         */
        unflatten: function(data, separator){
            var result = {};

            separator = separator || '.';

            _.each(data, function(value, nodes){
                nodes = nodes.split(separator);

                setNested(result, nodes, value);
            });

            return result;
        },

        /**
         * Same operation as 'flatten' method,
         * but returns objects' keys wrapped in '[]'.
         *
         * @param {Object} data - Object that should be serialized.
         * @returns {Object} Serialized data.
         *
         * @example
         *      utils.serialize({one: { two: { three: 'value'} }});
         *      => { 'one[two][three]': 'value' }
         */
        serialize: function(data){
            var result = {};

            data = this.flatten(data);

            _.each(data, function(value, keys){
                keys    = this.serializeName(keys);
                value   = _.isUndefined(value) ? '' : value;

                result[keys] = value;
            }, this);

            return result;
        },

        /**
         * Applies provided data to the template.
         *
         * @param {(String|Object)} template
         * @param {Object} source - Data object to match with template.
         * @returns {String|Object}
         *
         * @example Template defined as a string.
         *      var source = { foo: 'Random Stuff', bar: 'Some' };
         *      
         *      utils.template('{bar} {foo}', source);
         *      => 'Some Random Stuff';
         *
         * @example Example of template defined as object.
         *      var tpl = { key: { '{bar}_Baz': '{foo}' } };
         *
         *      utils.template(tpl, source);
         *      => { key: { 'Some_Baz': 'Random Stuff' } };
         */
        template: function(templ, source){
            var result,
                parse;

            if(_.isObject(templ)){
                templ   = JSON.stringify(templ);
                parse   = true;
            }

            result = template(templ, source);

            return parse ? JSON.parse(result) : result;
        }
    });
    
    /**
     * Helpers for working with strings.
     */
    _.extend(utils, {
        /**
         * Splits string by separator if it's possible,
         * otherwise returns the incoming value.
         *
         * @param {(String|Array|*)} str - String to split. 
         * @param {String} [separator=' '] - Seperator based on which to split the string. 
         * @returns {Array|*} Splitted string or the incoming value.
         */
        stringToArray: function(str, separator){
            separator = separator || ' ';

            return typeof str === 'string' ?
                str.split(separator) :
                str;
        },

        /**
         * Converts the incoming string which consists
         * of a specified delimiters into a format commonly used in form elements.
         *
         * @param {String} name - The incoming string.
         * @param {String} [separator='.']
         * @returns {String} Serialized string.
         *
         * @example
         *      utils.serializeName('one.two.three');
         *      => 'one[two][three]';
         */
        serializeName: function(name, separator){
            var result;

            separator   = separator || '.';
            name        = name.split(separator);

            result = name.shift();

            name.forEach(function(part){
                result += '[' + part + ']';
            });

            return result;
        }
    });
    
    /**
     * Array manipulation methods.
     */
    _.extend(utils, {
        /**
         * Facade method to remove/add value from/to array
         * without creating a new instance.
         *
         * @param {Array} arr - Array to be modified. 
         * @param {*} value - Value to add/remove.
         * @param {Boolean} add - Flag that specfies operation.
         * @returns {Utils} Chainable.
         */
        toggle: function(arr, value, add){
            return add ? 
                this.add(arr, value) :
                this.remove(arr, value);
        },

        /**
         * Removes the incoming value from array in case
         * without creating a new instance of it.
         *
         * @param {Array} arr - Array to be modified.
         * @param {*} value - Value to be removed.
         * @returns {Utils} Chainable.
         */
        remove: function(arr, value){
            var index = arr.indexOf(value);

            if(~index){
                arr.splice(index, 1);
            }

            return this;
        },

        /**
         * Adds the incoming value to array if
         * it's not alredy present in there.
         *
         * @param {Array} arr - Array to be modifed. 
         * @param {...*} Values to be added.
         * @returns {Utils} Chainable.
         */
        add: function(arr){
            var values = _.toArray(arguments).slice(1);

            values.forEach(function(value){
                if(!~arr.indexOf(value)){
                    arr.push(value);
                }
            });

            return this;
        },

        /**
         * Extends an incoming array with a specified ammount of undefined values
         * starting from a specified position.
         *
         * @param {Array} container - Array to be extended.
         * @param {Number} size - Ammount of values to be added.
         * @param {Number} [offset=0] - Position at which to start inserting values.
         * @returns {Array} Modified array.
         */
        reserve: function(container, size, offset){
            container.splice(offset || 0, 0, new Array(size));

            return _.flatten(container);
        },

        /**
         * Compares multiple arrays without tracking order of their elements.
         *
         * @param {...Array} Multiple arrays to compare.
         * @returns {Bollean} True if arrays are identical to each other.
         */
        identical: function(){
            var arrays  = _.toArray(arguments),
                first   = arrays.shift();

            return arrays.every(function(arr) {
                return (
                    arr.length === first.length &&
                    !_.difference(arr, first).length
                );
            });
        }
    });

    /**
     * Miscellaneous.
     */
    _.extend(utils, {
        /**
         * Generates a unique identifier.
         *
         * @param {Number} [size=7] - Length of a resulting identifier.
         * @returns {String}
         */
        uniqueid: function (size) {
            var code    = (Math.random() * 25 + 65) | 0,
                idstr   = String.fromCharCode(code);

            size = size || 7;

            while (idstr.length < size) {
                code = Math.floor((Math.random() * 42) + 48);

                if (code < 58 || code > 64) {
                    idstr += String.fromCharCode(code);
                }
            }

            return idstr;
        },

        /**
         * Serializes and sends data via POST request.
         *
         * @param {Object} options -
         *      Options object that consists of
         *      a 'url' and 'data' properties. 
         */
        submit: function(options){
            var form = document.createElement('form'),
                data = this.serialize(options.data),
                field;

            form.setAttribute('action', options.url);
            form.setAttribute('method', 'post');

            _.each(data, function(value, name){
                field = document.createElement('input');

                field.setAttribute('name', name);
                field.setAttribute('type', 'hidden');

                field.value = value;

                form.appendChild(field);
            });

            document.body.appendChild(form);

            form.submit();
        }
    });

    return utils;
});