/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

            return this;
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