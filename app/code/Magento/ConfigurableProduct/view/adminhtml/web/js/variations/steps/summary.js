/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore'
], function (Component, $, ko, _) {
    'use strict';

    var viewModel;
    viewModel = Component.extend({
        sections: ko.observableArray([]),
        attributes: ko.observableArray([]),
        render: function (wizard) {
            viewModel.prototype.sections(wizard.data.sections());
            viewModel.prototype.attributes(wizard.data.attributes());
        },
        force: function (wizard) {
        },
        back: function (wizard) {
        }
    });
    return viewModel;
});
