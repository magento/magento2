/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/collapsible'
], function (Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/controls/view',
            sampleData: [{
                label: 'Cameras'
            }, {
                label: 'Products by weight'
            }, {
                label: 'Greg\'s view'
            }, {
                label: 'Default View'
            }]
        }
    });
});
