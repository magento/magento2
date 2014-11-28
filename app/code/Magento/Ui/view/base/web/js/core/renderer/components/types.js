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

    return Class.extend({
        initialize: function(types){
            this.types = {};

            this.set(types);
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