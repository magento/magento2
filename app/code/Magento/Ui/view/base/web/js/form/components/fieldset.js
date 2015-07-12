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
            template: 'ui/form/fieldset',
            collapsible: false,
            opened: true
        }
    });
});
