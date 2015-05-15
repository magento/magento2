/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiComponent'
], function ($, _, utils, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            listens: {
                params: 'reload'
            }
        },

        initialize: function () {
            utils.limit(this, 'reload', 200);
            _.bindAll(this, 'onReload');

            return this._super();
        },

        initConfig: function () {
            this._super();

            _.extend(this.data, {
                items: [],
                totalRecords: 0
            });

            return this;
        },

        reload: function () {
            this.trigger('reload');

            $.ajax({
                url: this.update_url,
                method: 'GET',
                data: this.get('params'),
                dataType: 'json'
            }).done(this.onReload);
        },

        onReload: function (data) {
            this.set('data', data)
                .trigger('reloaded');
        }
    });
});
