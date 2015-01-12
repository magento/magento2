/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/utils'
], function(_, utils) {
    'use strict';

    var utils = {},
        atobSupport,
        btoaSupport;
    
    atobSupport = typeof atob === 'function';
    btoaSupport = typeof btoa === 'function';

    /** 
     * Base64 encoding/decoding methods.
     * First check for native support.
     */
    if( btoaSupport && atobSupport ){
         _.extend(utils, {
            atob: function(input){
                return window.atob(input);
            },

            btoa: function(input){
                return window.btoa(input);
            }
        });
    }
    else{
        _.extend(utils, {
            atob: function(input){
                return Base64.decode(input)
            },

            btoa: function(input){
                return Base64.encode(input);
            }
        });
    }    

    /**
     * Submits specified data as a form object.
     * @param {Object} params - Parameters of form.
     */
    utils.submitAsForm = function(params){  
        var form,
            field;

        form = document.createElement('form');

        form.setAttribute('method', params.method);
        form.setAttribute('action', params.action);

        _.each(params.data, function(value, name){
            field = document.createElement('input');

            if(typeof value === 'object'){
                value = JSON.stringify(value);
            }

            field.setAttribute('name', name);
            field.setAttribute('type', 'hidden');
            
            field.value = value;

            form.appendChild(field);
        });

        document.body.appendChild(form);

        form.submit();
    };

    return utils;
});