/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'rjsResolver',
    './adapter',
    'uiCollection',
    'mageUtils',
    'jquery',
    'Magento_Ui/js/core/app',
    'mage/validation'
], function (_, loader, resolver, adapter, Collection, utils, $, app) {
    'use strict';

    /**
     * Format params
     *
     * @param {Object} params
     * @returns {Array}
     */
    function prepareParams(params) {
        var result = '?';

        _.each(params, function (value, key) {
            result += key + '=' + value + '&';
        });

        return result.slice(0, -1);
    }

    /**
     * Collect form data.
     *
     * @param {Array} items
     * @returns {Object}
     */
    function collectData(items) {
        var result = {},
            name;

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

                case 'select-multiple':
                    name = item.name.substring(0, item.name.length - 2); //remove [] from the name ending
                    result[name] = _.pluck(item.selectedOptions, 'value');
                    break;

                default:
                    result[item.name] = item.value;
            }
        });

        return result;
    }

    /**
     * Makes ajax request
     *
     * @param {Object} params
     * @param {Object} data
     * @param {String} url
     * @returns {*}
     */
    function makeRequest(params, data, url) {
        var save = $.Deferred();

        data = utils.serialize(data);
        data['form_key'] = window.FORM_KEY;

        if (!url) {
            save.resolve();
        }

        $('body').trigger('processStart');

        $.ajax({
            url: url + prepareParams(params),
            data: data,
            dataType: 'json',

            /**
             * Success callback.
             * @param {Object} resp
             * @returns {Boolean}
             */
            success: function (resp) {
                if (resp.ajaxExpired) {
                    window.location.href = resp.ajaxRedirect;
                }

                if (!resp.error) {
                    save.resolve(resp);

                    return true;
                }

                $('body').notification('clear');
                $.each(resp.messages, function (key, message) {
                    $('body').notification('add', {
                        error: resp.error,
                        message: message,

                        /**
                         * Inserts message on page
                         * @param {String} msg
                         */
                        insertMethod: function (msg) {
                            $('.page-main-actions').after(msg);
                        }
                    });
                });
            },

            /**
             * Complete callback.
             */
            complete: function () {
                $('body').trigger('processStop');
            }
        });

        return save.promise();
    }

    /**
     * Check if fields is valid.
     *
     * @param {Array}items
     * @returns {Boolean}
     */
    function isValidFields(items) {
        var result = true;

        _.each(items, function (item) {
            if (!$.validator.validateSingleElement(item)) {
                result = false;
            }
        });

        return result;
    }

    return Collection.extend({
        defaults: {
            additionalFields: [],
            additionalInvalid: false,
            selectorPrefix: '.page-content',
            messagesClass: 'messages',
            errorClass: '.admin__field._error',
            eventPrefix: '.${ $.index }',
            ajaxSave: false,
            ajaxSaveType: 'default',
            imports: {
                reloadUrl: '${ $.provider}:reloadUrl'
            },
            listens: {
                selectorPrefix: 'destroyAdapter initAdapter',
                '${ $.name }.${ $.reloadItem }': 'params.set reload'
            },
            exports: {
                selectorPrefix: '${ $.provider }:client.selectorPrefix',
                messagesClass: '${ $.provider }:client.messagesClass'
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
                'save': this.save.bind(this, true, {}),
                'saveAndContinue': this.save.bind(this, false, {})
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

            if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                this.setAdditionalData(data)
                    .submit(redirect);
            } else {
                this.focusInvalid();
            }
        },

        /**
         * Tries to set focus on first invalid form field.
         *
         * @returns {Object}
         */
        focusInvalid: function () {
            var invalidField = _.find(this.delegate('checkInvalid'));

            if (!_.isUndefined(invalidField) && _.isFunction(invalidField.focused)) {
                invalidField.focused(true);
            }

            return this;
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
            var additional = collectData(this.additionalFields),
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
            this.additionalFields = document.querySelectorAll(this.selector);
            this.source.set('params.invalid', false);
            this.source.trigger('data.validate');
            this.set('additionalInvalid', !isValidFields(this.additionalFields));
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
        },

        /**
         * Updates data from server.
         */
        reload: function () {
            makeRequest(this.params, this.data, this.reloadUrl).then(function (data) {
                app(data, true);
            });
        }
    });
});
