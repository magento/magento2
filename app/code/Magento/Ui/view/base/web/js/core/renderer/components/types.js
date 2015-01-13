/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'mage/utils',
    'Magento_Ui/js/lib/class',
    'Magento_Ui/js/lib/registry/registry'
], function(_, $, utils, Class, registry) {
    'use strict';

    return Class.extend({
        initialize: function(types){
            this.types = {};

            this.set(types);

            return this;
        },

        set: function(types){
            types = types || [];
            
            _.each(types, function(data, type){
                this.types[type] = this.flatten(data);
            }, this);
        },

        get: function(type){
            return this.types[type] || {};
        },

        flatten: function(data){
            var extender = data.extends || [],
                result   = {};

            extender = utils.stringToArray(extender);

            extender.push(data);

            extender.forEach(function(item){
                if(_.isString(item)){
                    item = this.get(item);
                }

                $.extend(true, result, item);
            }, this);

            delete result.extends;

            return result
        }
    });
});