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
    'underscore'
], function(_) {
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