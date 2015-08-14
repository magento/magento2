/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, _, utils, Component, alert, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            listens: {
                params: 'reload'
            }
        },

        /**
         * initialize provider
         */
        initialize: function () {
            utils.limit(this, 'reload', 200);
            _.bindAll(this, 'onReload');

            return this._super();
        },

        /**
         * initialize config
         */
        initConfig: function () {
            this._super();

            _.extend(this.data, {
                items: [],
                totalRecords: 0
            });

            return this;
        },

        /**
         * reload data from server
         */
        reload: function () {
            this.trigger('reload');
            $.ajax({
                url: this.update_url,
                method: 'GET',
                data: this.get('params'),
                dataType: 'json'
            })
                .error(this.onError)
                .done(this.onReload);
        },

        /**
         * alert with error message
         */
        onError: function () {
            alert({
                content: $t('Something go wrong')
            });
        },

        /**
         * set data and triggered reloaded
         */
        onReload: function (data) {
            this.set('data', data)
                .trigger('reloaded');
        }
    });
});
