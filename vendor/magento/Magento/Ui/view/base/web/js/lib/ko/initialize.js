/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/** Loads all available knockout bindings, sets custom template engine, initializes knockout on page */
define([
    'ko',
    './template/engine',
    './bind/date',
    './bind/scope',
    './bind/datepicker',
    './bind/stop_propagation',
    './bind/outer_click',
    './bind/class',
    './bind/keyboard',
    './bind/optgroup',
    './extender/observable_array'
], function(ko, templateEngine) {
    'use strict';

    ko.setTemplateEngine(templateEngine);
    ko.applyBindings();

});