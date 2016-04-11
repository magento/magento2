/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiClass'
], function ($, _, utils, Class) {
    'use strict';

    function beforeSave(data, url) {
        var save = $.Deferred();

        data = utils.serialize(data);

        data.form_key = window.FORM_KEY;

        if (!url) {
            save.resolve();
        }

        $('body').trigger('processStart');

        $.ajax({
            url: url,
            data: data,
            success: function (resp) {
                if (!resp.error) {
                    save.resolve();
                    return true;
                }

                $('body').notification('clear');
                $.each(resp.messages, function(key, message) {
                    $('body').notification('add', {
                        error: resp.error,
                        message: message,
                        insertMethod: function(message) {
                            $('.page-main-actions').after(message);
                        }
                    });
                });
            },
            complete: function () {
                $('body').trigger('processStop');
            }
        });

        return save.promise();
    }

    return Class.extend({

        /**
         * Assembles data and submits it using 'utils.submit' method
         */
        save: function (data, options) {
            var url = this.urls.beforeSave,
                save = this._save.bind(this, data, options);

            beforeSave(data, url).then(save);

            return this;
        },

        _save: function (data, options) {
            var url = this.urls.save;

            options = options || {};

            if (!options.redirect) {
                url += 'back/edit';
            }

            utils.submit({
                url: url,
                data: data
            }, options.attributes);

            return this;
        }
    });
});
