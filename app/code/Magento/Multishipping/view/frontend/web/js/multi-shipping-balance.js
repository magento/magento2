/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/dataPost',
    'jquery-ui-modules/widget'
], function ($, dataPost) {
    'use strict';

    $.widget('mage.multiShippingBalance', {
        options: {
            changeUrl: ''
        },

        /**
         * Initialize balance checkbox events.
         *
         * @private
         */
        _create: function () {
            this.element.on('change', $.proxy(function (event) {
                dataPost().postData({
                    action: this.options.changeUrl,
                    data: {
                        useBalance: +$(event.target).is(':checked')
                    }
                });
            }, this));
        }
    });

    return $.mage.multiShippingBalance;
});
