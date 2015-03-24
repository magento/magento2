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
            utils.limit(this, 'reload', 100);
            _.bindAll(this, 'onReload');

            return this._super();
        },

        reload: function () {
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
