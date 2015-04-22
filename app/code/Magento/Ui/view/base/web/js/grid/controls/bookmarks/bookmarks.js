/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'Magento_Ui/js/core/renderer/layout'
], function (_, utils, Collapsible, layout) {
    'use strict';

    var itemTmpl = {
        parent: '<%= $data.name %>',
        name: '<%= $data.value %>',
        label: '<%= $data.label %>',
        component: 'Magento_Ui/js/grid/controls/bookmarks/item'
    };

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/bookmarks/bookmarks',
            sampleData: [{
                label: 'Cameras',
                value: 'cameras'
            }, {
                label: 'Products by weight',
                value: 'products'
            }, {
                label: 'Greg\'s view',
                value: 'greg'
            }, {
                label: 'Default View',
                value: 'default'
            }]
        },

        initialize: function () {
            this._super()
                .createChildren();

            return this;
        },

        createChildren: function () {
            var data;

            this.sampleData.forEach(function (item) {
                data = _.extend({}, this, item);

                layout([utils.template(itemTmpl, data)]);
            }, this);
        }
    });
});
