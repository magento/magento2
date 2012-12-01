/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    $.widget("mage.form", {
        options: {
            actionTemplate: '${base}{{each(key, value) args}}${key}/${value}/{{/each}}',
            handlersData: {
                save: {},
                saveAndContinueEdit: {
                    action: {
                        args: {back: 'edit'}
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
            $.template('actionTemplate', this.options.actionTemplate);
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
                var prop = this.element.prop(attrName);
                this.oldAttributes[attrName] = prop ? prop : '';
            }
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            this.element.on(this._getHandlers().join(' '), $.proxy(this._submit, this));
        },

        /**
         * Get action url for form
         * @param {Object|string} data object with parameters for action url or url string
         * @return {string} action url
         */
        _getActionUrl: function(data) {
            if ($.type(data) === 'object') {
                return $.tmpl('actionTemplate', {
                    base: this.oldAttributes.action,
                    args: data.args
                }).text();
            } else {
                return $.type(data) === 'string' ? data : this.oldAttributes.action;
            }
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
                if(attrName === 'action') {
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
            this.element.trigger('beforeSubmit', submitData);
            data = $.extend(
                true,
                {},
                this.options.handlersData[handlerName] || {},
                submitData,
                data
            );
            this.element.prop(this._processData(data));
        },

        /**
         * Submit the form
         * @param {Object} e event object
         * @param {Object} data event data object
         */
        _submit: function(e, data) {
            this._rollback();
            this._beforeSubmit(e.type, data);
            this.element.triggerHandler('submit');
        }
    });

    $.widget('ui.button', $.ui.button, {
        /**
         * Button creation
         * @protected
         */
        _create: function() {
            this._processDataAttr();
            this._bind();
            this._super("_create");
        },

        /**
         * Get additional options from data attribute and merge it in this.options
         * @protected
         */
        _processDataAttr: function() {
            var data = this.element.data().widgetButton;
            $.extend(true, this.options, $.type(data) === 'object' ? data : {});
        },

        /**
         * Bind handler on button click
         * @protected
         */
        _bind: function() {
            this.element.on('click', $.proxy(function() {
                $(this.options.related)
                    .trigger(this.options.event, this.options.eventData ? [this.options.eventData] : [{}]);
            }, this));
        }
    });
})(jQuery);
