/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './core',
    './links',
    './manip',
    './traversal',
    './provider',
    'Magento_Ui/js/lib/class',
    'Magento_Ui/js/lib/ko/initialize'
], function (_, core, links, manip, traversal, provider, Class) {
    'use strict';

    var extenders;

    extenders = _.extend({}, core, links, manip, traversal, provider);

    return Class.extend(extenders);
});
