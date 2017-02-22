/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Loads all available knockout bindings, sets custom template engine, initializes knockout on page */

define([
    'ko',
    './template/engine',
    'knockoutjs/knockout-repeat',
    'knockoutjs/knockout-fast-foreach',
    'knockoutjs/knockout-es5',
    './bind/scope',
    './bind/staticChecked',
    './bind/datepicker',
    './bind/outer_click',
    './bind/keyboard',
    './bind/optgroup',
    './bind/fadeVisible',
    './bind/mage-init',
    './bind/after-render',
    './bind/i18n',
    './bind/collapsible',
    './bind/autoselect',
    './extender/observable_array',
    './extender/bound-nodes'
], function (ko, templateEngine) {
    'use strict';

    ko.setTemplateEngine(templateEngine);
    ko.applyBindings();
});
