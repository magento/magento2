/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './collapsible'
], function(Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/fieldset/fieldset'
        }
    });
});