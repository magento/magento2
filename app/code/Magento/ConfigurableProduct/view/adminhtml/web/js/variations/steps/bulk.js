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

    //TODO: where unique id for options
    var viewModel;
    var vm = {
        attributes: [
            {
                id: '12',
                label: 'color',
                options: [
                    {
                        label: 'gray',
                        value: '12gray'
                    },
                    {
                        label: 'blue',
                        value: null
                    }
                ]
            },
            {
                id: '155',
                label: 'size',
                options: [
                    {
                        label: 'm',
                        value: ''
                    },
                    {
                        label: 's',
                        value: 'xxx'
                    }
                ]
            }
        ],
        sections: {
            images: {
                type: 'each',
                value: {
                    'gray': ['img1', 'img2'],
                    'blue': null
                },
                attribute: 12
            },
            pricing: {
                type: 'single',
                value: 100
            },
            inventory: {
                type: 'none',
                value: 0
            }
        }
    };

    viewModel = Component.extend({
        attributes: ko.observableArray([]),
        sections: ko.observableArray([
            {
                label: 'images',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            },
            {
                label: 'pricing',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            },
            {
                label: 'inventory',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            }
        ]),
        render: function (wizard) {
            viewModel.prototype.attributes(wizard.data.attributesValues);
        },
        force: function (wizard) {
        },
        back: function (wizard) {
        }
    });
    return viewModel;
});
