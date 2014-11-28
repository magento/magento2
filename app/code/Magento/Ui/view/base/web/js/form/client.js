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
    'jquery',
    'underscore',
    'mage/utils',
    'Magento_Ui/js/lib/class'
], function($, _, utils, Class){
    'use strict';
    
    var defaults = {};

    function beforeSave(data, url){
        var save = $.Deferred();
        
        data = utils.serialize(data);

        data.form_key = FORM_KEY;
        
        if(!url){
            save.resolve();
        }

        $('body').trigger('processStart');

        $.ajax({
            url: url,
            data: data,
            success: function(resp){
                if(!resp.error){
                    save.resolve();
                }
            },
            complete: function(){
                $('body').trigger('processStop');
            }
        });

        return save.promise();
    }

    return Class.extend({
        /**
         * Initializes DataProvider instance.
         * @param {Object} settings - Settings to initialize object with.
         */
        initialize: function(config) {
            _.extend(this, defaults, config);
        },

        /**
         * Assembles data and submits it using 'utils.submit' method
         */
        save: function(data, options){
            var url     = this.urls.beforeSave,
                save    = this._save.bind(this, data, options);

            beforeSave(data, url).then(save);

            return this;
        },

        _save: function(data, options){
            var url = this.urls.save;

            options = options || {};

            data.form_key = FORM_KEY;

            if(!options.redirect){
                url += 'back/edit';
            }

            utils.submit({
                url:    url,
                data:   data
            });

            return this;
        }
    });
});