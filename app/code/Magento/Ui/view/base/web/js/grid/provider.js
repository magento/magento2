/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/provider'
], function ($, _, utils, Provider) {
    'use strict';

    return Provider.extend({
        initialize: function () {
            _.bindAll(this, '_reload', 'onReload');

            return this._super();
        },

        reload: function () {
            if (this.timeoutID) {
                window.clearTimeout(this.timeoutID);
            }

            this.timeoutID = window.setTimeout(this._reload, 100);
        },

        _reload: function () {
            delete this.timeoutID;

            this.trigger('reload');

            $.ajax({
                url: this.data.update_url,
                method: 'GET',
                data: this.get('params'),
                dataType: 'json'
            }).done(this.onReload);
        },

        onReload: function (data) {
            this.set('data', data);
            this.trigger('reloaded');
        }
    });
});
