/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'mageUtils',
    './abstract'
], function (utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            value: '',
            links: {
                value: ''
            }
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
         * Set the file input value
         *
         * @param {FileUploader} fileUploader - UI Class
         * @param {Event} e
         */
        setFileValue: function (fileUploader, e) {
            this.value(e.target.files.length ? e.target.files.length : '');
        }
    });
});
