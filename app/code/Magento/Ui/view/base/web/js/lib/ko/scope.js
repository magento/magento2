/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    '../class',
    './initialize'
], function(ko, _, Class) {
    'use strict';

    /**
     * Wrapper for ko.observable and ko.observableArray.
     * Assignes one or another ko property to obj[key]
     * @param  {Object} obj   - object to store property to
     * @param  {String} key   - key
     * @param  {*} value      - initial value of observable
     */
    function observe(obj, key, value){
        var method = Array.isArray(value) ? 'observableArray' : 'observable';

        obj[key] = ko[method](value);
    }

    return Class.extend({

        /**
         * If 2 params passed, path is considered as key.
         * Else, path is considered as object.
         * Assignes props to this based on incoming params
         * @param  {Object|String} path
         * @param  {*} value
         */
        observe: function(path, value) {
            var type = typeof path;

            if(arguments.length === 1){
                if(type === 'string'){
                    path = path.split(' ');
                }

                if(Array.isArray(path)){
                    path.forEach(function(key){
                        observe(this, key, this[key]);
                    }, this);
                }
                else if(type==='object'){
                    _.each(path, function(value, key){
                        observe(this, key, value);
                    }, this);
                }
            }
            else if(type === 'string') {
                observe(this, path, value);
            }

            return this;
        },

        /**
         * Reads it's params from provider and stores it into its params object
         * @return {Object} reference to instance
         */
        pushParams: function(){
            var params      = this.params,
                provider    = this.provider.params,
                data        = {};

            params.items.forEach(function(name) {
                data[name] = this[name]();
            }, this);

            provider.set(params.dir, data);

            return this;
        },

        /**
         * Loops over params.items and writes it's corresponding {key: value} 
         * pairs to this as observables.
         * @return {Object} reference to instance
         */
        pullParams: function(){
            var params      = this.params,
                provider    = this.provider.params,
                data        = provider.get(params.dir);

            params.items.forEach(function(name) {
                this[name](data[name]);
            }, this);

            return this;
        },

        /**
         * Calls pushParams and calls refresh on this.provider
         */
        reload: function() {
            this.pushParams()
                .provider.refresh();
        }
    });
});