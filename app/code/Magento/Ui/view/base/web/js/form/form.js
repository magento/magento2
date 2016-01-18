/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'rjsResolver',
    './adapter',
    'uiCollection'
], function (_, loader, resolver, adapter, Collection) {
    'use strict';

    /**
     * Collect form data.
     *
     * @param {String} selector
     * @returns {Object}
     */
    function collectData(selector) {
        var items = document.querySelectorAll(selector),
            result = {};

        items = Array.prototype.slice.call(items);

        items.forEach(function (item) {
            switch (item.type) {
                case 'checkbox':
                    result[item.name] = +!!item.checked;
                    break;

                case 'radio':
                    if (item.checked) {
                        result[item.name] = item.value;
                    }
                    break;

                default:
                    result[item.name] = item.value;
            }
        });

        return result;
    }

    return Collection.extend({
        defaults: {
            selectorPrefix: false,
            eventPrefix: '.${ $.index }',
            ajaxSave: false,
            ajaxSaveType: 'default',
            listens: {
                selectorPrefix: 'destroyAdapter initAdapter'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initAdapter();

            resolver(this.hideLoader, this);

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'responseData',
                    'responseStatus'
                ]);
        },

        /** @inheritdoc */
        initConfig: function () {
            this._super();

            this.selector = '[data-form-part=' + this.namespace + ']';

            return this;
        },

        /**
         * Initialize adapter handlers.
         *
         * @returns {Object}
         */
        initAdapter: function () {
            adapter.on({
                'reset': this.reset.bind(this),
                'save': this.save.bind(this, true),
                'saveAndContinue': this.save.bind(this, false)
            }, this.selectorPrefix, this.eventPrefix);

            return this;
        },

        /**
         * Destroy adapter handlers.
         *
         * @returns {Object}
         */
        destroyAdapter: function () {
            adapter.off([
                'reset',
                'save',
                'saveAndContinue'
            ], this.eventPrefix);

            return this;
        },

        /**
         * Hide loader.
         *
         * @returns {Object}
         */
        hideLoader: function () {
            loader.get(this.name).hide();

            return this;
        },

        /**
         * Validate and save form.
         *
         * @param {String} redirect
         * @param {Object} data
         */
        save: function (redirect, data) {
            this.validate();

            if (!this.source.get('params.invalid')) {
                this.setAdditionalData(data)
                    .submit(redirect);
            }
        },

        /**
         * Set additional data to source before form submit and after validation.
         *
         * @param {Object} data
         * @returns {Object}
         */
        setAdditionalData: function (data) {
            _.each(data, function (value, name) {
                this.source.set('data.' + name, value);
            }, this);

            return this;
        },

        /**
         * Submits form
         *
         * @param {String} redirect
         */
        submit: function (redirect) {
            var additional = collectData(this.selector),
                source = this.source;

            _.each(additional, function (value, name) {
                source.set('data.' + name, value);
            });

            source.save({
                redirect: redirect,
                ajaxSave: this.ajaxSave,
                ajaxSaveType: this.ajaxSaveType,
                response: {
                    data: this.responseData,
                    status: this.responseStatus
                },
                attributes: {
                    id: this.namespace
                }
            });
        },

        /**
         * Validates each element and returns true, if all elements are valid.
         */
        validate: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('data.validate');
        },

        /**
         * Trigger reset form data.
         */
        reset: function () {
            this.source.trigger('data.reset');
        },

        /**
         * Trigger overload form data.
         */
        overload: function () {
            this.source.trigger('data.overload');
        }
    });
});
