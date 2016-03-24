/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-undef */
define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'ko',
    './abstract',
    'Magento_Variable/variables'
], function ($, _, ko, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            elementSelector: 'textarea',
            value: '',
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            },
            listens: {
                '${ $.provider }:data.save': 'onSave'
            },
            template: 'ui/form/field',
            elementTmpl: 'ui/form/element/wysiwyg',
            content:        '',
            showSpinner:    false,
            loading:        false
        },

        /**
         * Call method save() for tinyMCE instance before submit data
         */
        onSave: function () {
            var tinyEditor = tinyMCE || null;

            if (tinyEditor) {
                tinyEditor.editors[this.textarea.id].save();
            }
        },

        /**
         *
         * @returns {} Chainable.
         */
        initialize: function () {
            this._super()
                .initNodeListener();

            return this;
        },

        /**
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super()
                .observe('value');

            return this;
        },

        /**
         *
         * @returns {} Chainable.
         */
        initNodeListener: function () {
            $.async({
                component: this,
                selector: this.elementSelector
            }, this.setElementNode.bind(this));

            return this;
        },

        /**
         *
         * @param {HTMLElement} node
         */
        setElementNode: function (node) {
            this.textarea = node;
            $(node).bindings({
                value: this.value
            });
        }
    });
});
