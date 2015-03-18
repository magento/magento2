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
        reload: function () {
            if (this.timeoutID) {
                window.clearTimeout(this.timeoutID);
            }

            window.setTimeout(this._reload.bind(this), 200);
        },

        _reload: function () {
            delete this.timeoutID;

            this.trigger('reload');

            $.ajax({
                url: this.data.update_url,
                method: 'GET',
                data: this.get('params'),
                dataType: 'json'
            }).done(this.onReload.bind(this));
        },

        onReload: function (data) {
            this.set('data', data);
            this.tirgger('reloaded');
        }
    });
});
