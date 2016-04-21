/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "jquery/ui"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    $.widget("mage.form", {
        options: {
            handlersData: {
                save: {},
                saveAndContinueEdit: {
                    action: {
                        args: {
                            back: 'edit'
                        }
                    }
                },
                preview: {
                    target: '_blank'
                }
            }
        },

        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Set form attributes to initial state
         * @protected
         */
        _rollback: function() {
            if (this.oldAttributes) {
                this.element.prop(this.oldAttributes);
            }
        },

        /**
         * Check if field value is changed
         * @protected
         * @param {Object} e event object
         */
        _changesObserver: function(e) {
            var target = $(e.target);
            if (e.type === 'focus' || e.type === 'focusin') {
                this.currentField = {
                    statuses: {
                        checked: target.is(':checked'),
                        selected: target.is(':selected')
                    },
                    val: target.val()
                };

            } else {
                if (this.currentField) {
                    var changed = target.val() !== this.currentField.val ||
                        target.is(':checked') !== this.currentField.statuses.checked ||
                        target.is(':selected') !== this.currentField.statuses.selected;
                    if (changed) {
                        target.trigger('changed');
                    }
                }
            }
        },
        /**
         * Get array with handler names
         * @protected
         * @return {Array} Array of handler names
         */
        _getHandlers: function() {
            var handlers = [];
            $.each(this.options.handlersData, function(key) {
                handlers.push(key);
            });
            return handlers;
        },

        /**
         * Store initial value of form attribute
         * @param {string} attrName name of attribute
         * @protected
         */
        _storeAttribute: function(attrName) {
            this.oldAttributes = this.oldAttributes || {};
            if (!this.oldAttributes[attrName]) {
                var prop = this.element.attr(attrName);
                this.oldAttributes[attrName] = prop ? prop : '';
            }
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            this.element
                .on(this._getHandlers().join(' '), $.proxy(this._submit, this))
                .on('focus blur focusin focusout', $.proxy(this._changesObserver, this));
        },

        /**
         * Get action url for form
         * @param {Object|string} data object with parameters for action url or url string
         * @return {string} action url
         */
        _getActionUrl: function(data) {
            if ($.type(data) === 'object') {
                return this._buildURL(this.oldAttributes.action, data.args);
            } else {
                return $.type(data) === 'string' ? data : this.oldAttributes.action;
            }
        },

        /**
         * Add additional parameters into URL
         * @param {string} url - original url
         * @param {Object} params - object with parameters for action url
         * @return {string} action url
         * @private
         */
        _buildURL: function(url, params) {
            var concat = /\?/.test(url) ? ['&', '='] : ['/', '/'];
            url = url.replace(/[\/&]+$/, '');
            $.each(params, function(key, value) {
                url += concat[0] + key + concat[1] + window.encodeURIComponent(value);
            });
            return url + (concat[0] === '/' ? '/' : '');
        },

        /**
         * Prepare data for form attributes
         * @protected
         * @param {Object}
         * @return {Object}
         */
        _processData: function(data) {
            $.each(data, $.proxy(function(attrName, attrValue) {
                this._storeAttribute(attrName);
                if (attrName === 'action') {
                    data[attrName] = this._getActionUrl(attrValue);
                }
            }, this));
            return data;
        },

        /**
         * Get additional data before form submit
         * @protected
         * @param {string}
         * @param {Object}
         */
        _beforeSubmit: function(handlerName, data) {
            var submitData = {};
            var event = new $.Event('beforeSubmit');
            this.element.trigger(event, [submitData, handlerName]);
            data = $.extend(
                true, {},
                this.options.handlersData[handlerName] || {},
                submitData,
                data
            );
            this.element.prop(this._processData(data));
            return !event.isDefaultPrevented();
        },

        /**
         * Submit the form
         * @param {Object} e event object
         * @param {Object} data event data object
         */
        _submit: function(e, data) {
            this._rollback();
            if (false !== this._beforeSubmit(e.type, data)) {
                this.element.trigger('submit', e);
            }
        }
    });
    
    return $.mage.form;
}));
