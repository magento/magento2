/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiClass'
], function ($, _, utils, Class) {
    'use strict';

    /**
     * Before save validate request.
     *
     * @param {Object} data
     * @param {String} url
     * @returns {*}
     */
    function beforeSave(data, url) {
        var save = $.Deferred();

        data = utils.serialize(data);

        data['form_key'] = window.FORM_KEY;

        if (!url || url === 'undefined') {
            return save.resolve();
        }

        $('body').trigger('processStart');

        $.ajax({
            url: url,
            data: data,

            /**
             * Success callback.
             * @param {Object} resp
             * @returns {Boolean}
             */
            success: function (resp) {
                if (!resp.error) {
                    save.resolve();

                    return true;
                }

                $('body').notification('clear');
                $.each(resp.messages, function (key, message) {
                    $('body').notification('add', {
                        error: resp.error,
                        message: message,

                        /**
                         * Insert method.
                         *
                         * @param {String} msg
                         */
                        insertMethod: function (msg) {
                            $('.page-main-actions').after(msg);
                        }
                    });
                });
            },

            /**
             * Complete callback.
             */
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

        /**
         * Save data.
         *
         * @param {Object} data
         * @param {Object} options
         * @returns {Object}
         * @private
         */
        _save: function (data, options) {
            var url = this.urls.save;

            options = options || {};

            if (!options.redirect) {
                url += 'back/edit';
            }

            if (options.ajaxSave) {
                utils.ajaxSubmit({
                    url: url,
                    data: data
                }, options);

                return this;
            }

            utils.submit({
                url: url,
                data: data
            }, options.attributes);

            return this;
        }
    });
});
