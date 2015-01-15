/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "jquery/template"
], function($){

    $.widget('vde.dialog', $.ui.dialog, {
        options: {
            text: {
                selector: '.confirm_message'
            },
            messages: {
                selector: '.messages'
            }
        },

        /**
         * Dialog creation
         * @protected
         */
        _create: function() {
            this._superApply(arguments);
            this.messages = {
                element: this.element.find(this.options.messages.selector),
                clear: function() {
                    this.element.html('');
                },
                add: function(message, type) {
                    if (message) {
                        message = this._prepareMessage(message, type);
                        this.element.append(message);
                    }
                },
                set: function(message, type) {
                    if (message) {
                        message = this._prepareMessage(message, type);
                        this.element.html(message);
                    }
                },
                _isValidType: function(type) {
                    return ['success', 'error', 'info'].indexOf(type) != -1;
                },
                _prepareMessage: function(message, type) {
                    if (typeof message != 'string' && message.message && message.type) {
                        type = message.type;
                        message = message.message;
                    }
                    if (type != undefined && !this._isValidType(type)) {
                        throw Error('Invalid type "' + type + '"');
                    }

                    if (this._isValidType(type)) {
                        var classes = ['message-' + type];

                        //Fix for messages of types 'error' and 'info'
                        ['error', 'info'].indexOf(type) != -1 && classes.unshift('message');

                        var vars = {
                            classes: classes.join(' '),
                            message: message
                        };
                        message = $.tmpl('<div class="${classes}">${message}</div>', vars);
                    }
                    return message;
                }
            };
            this.text = {
                element: this.element.find(this.options.text.selector),
                set: function(text) {
                    this.element.html(text);
                },
                clear: function() {
                    this.element.html('');
                }
            };
            this.title = {
                dialog: this,
                set: function(title) {
                    this.dialog._setOption('title', title);
                }
            }
        },

        /**
         * Set main params of confirmation dialog
         *
         * @param {string} title
         * @param {string} text
         * @param {Array.<Object>|Object} buttons
         */
        set: function (title, text, buttons) {
            title = $.mage.__(title);
            text = $.mage.__(text);

            this.text.set(text);
            this.title.set(title);
            this.setButtons(buttons);
        },

        /**
         * Set dialog buttons
         *
         * @param {Array.<Object>|Object|undefined} buttons
         * @param {boolean} addCancel
         */
        setButtons: function(buttons, addCancel) {
            if (buttons == undefined) {
                buttons = [];
            } else {
                if ($.type(buttons) !== 'array') {
                    buttons = [buttons];
                }
                buttons.each(function(button){
                    button.text = $.mage.__(button.text)
                });
            }

            var hasToAddCancel = (addCancel == undefined && buttons.length <= 1) || addCancel == true;
            if (hasToAddCancel) {
                buttons.unshift({
                    text: $.mage.__('Cancel'),
                    click: $.proxy(function() {
                        this.close();
                    }, this),
                    'class': 'action-close'
                });
            }

            this._setOption('buttons', buttons);
        },

        /**
         * Add buttons to dialog
         *
         * @param {Object} button
         * @param {number} position
         */
        addButton: function(button, position) {
            button.text = $.mage.__(button.text)

            var buttons = this.options.buttons;
            buttons.splice(position, 0, button);
            this._setOption('buttons', buttons);
        },

        /**
         * Remove button from dialog
         *
         * @param {string|number}buttonPointer
         */
        removeButton: function(buttonPointer) {
            var buttons = this.options.buttons;

            var position;
            if(/^\d+$/.test(buttonPointer)) {
                position = buttonPointer;
            } else {
                //Find 1st button with given title
                var title = $.mage.__(buttonPointer);
                this.options.buttons.each(function(button, index) {
                    if (button.text == title) {
                        position = index;
                        return false;
                    }
                });
            }

            buttons.splice(position, 1);
            this._setOption('buttons', buttons);
        }
    });
    
    return $.vde.dialog;
});