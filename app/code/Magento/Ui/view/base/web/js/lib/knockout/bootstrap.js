/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Loads all available knockout bindings, sets custom template engine, initializes knockout on page */

define([
    'ko',
    './template/engine',
    'knockoutjs/knockout-es5',
    './bindings/bootstrap',
    './extender/observable_array',
    './extender/bound-nodes',
    'domReady!'
], function (ko, templateEngine) {
    'use strict';

    ko.uid = 0;

    ko.setTemplateEngine(templateEngine);
    ko.applyBindings();
});
