/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function ($, _, Collapsible) {
    'use strict';
    console.log('one');
    return Collapsible.extend({

        defaults: {
            template: 'ui/grid/exportButton',
            listens: {
                actionValue: 'applyOption'
            }
        },

        initObservable: function () {
            this._super()
                .observe('actionValue');

            return this;
        },

        applyOption: function (actionId) {
            var action = this.getAction(actionId);
            location.href = action.url;
        },

        getAction: function (value) {
            return _.findWhere(this.options, {value: value});
        }
    });
});
