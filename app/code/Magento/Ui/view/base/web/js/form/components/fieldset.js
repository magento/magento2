/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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