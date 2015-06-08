/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (Component, $, ko, _, Collapsible) {
    'use strict';

    var viewModel = Component.extend({
        attributesValues: ko.observableArray([]),
        render: function(wizard) {
            viewModel.prototype.attributesValues(wizard.data.attributesValues);
        },
        force: function(wizard) {
        },
        back: function(wizard) {
        }
    });
    return viewModel;
});
