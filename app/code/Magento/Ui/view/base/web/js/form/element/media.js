/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    './abstract'
], function (utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            deleteCheckbox: false,
            links: {
                value: '',
                file: '${ $.provider }:${ $.dataScope }.file',
                type: '${ $.provider }:${ $.dataScope }.type',
                url: '${ $.provider }:${ $.dataScope }.url',
                deleteCheckbox: '${ $.provider }:${ $.dataScope }.delete'
            },
            width: 22,
            height: 22
        },

        /**
         * Initializes file component.
         *
         * @returns {Media} Chainable.
         */
        initialize: function () {
            this._super()
                .initFormId();

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            this._super();

            this.observe('deleteCheckbox');

            return this;
        },

        /**
         * Defines form ID with which file input will be associated.
         *
         * @returns {Media} Chainable.
         */
        initFormId: function () {
            var namespace;

            if (this.formId) {
                return this;
            }

            namespace   = this.name.split('.');
            this.formId = namespace[0];

            return this;
        },

        /**
         * Calls global image preview handler
         */
        callPreviewHandler: function() {
            imagePreview('image-' + this.uid);
        }
    });
});
