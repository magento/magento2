/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    "jquery",
    "jquery/ui"
], function($){
    'use strict';

    var timer = null;
    /**
     * Dropdown Widget - this widget is a wrapper for the jQuery UI Dialog
     */
    $.widget('mage.dropdownDialog', $.ui.dialog, {
        options: {
            triggerEvent : "click",
            triggerClass: null,
            parentClass: null,
            triggerTarget: null,
            defaultDialogClass: "mage-dropdown-dialog",
            dialogContentClass: null,
            closeOnMouseLeave: true,
            closeOnClickOutside: true,
            minHeight: null,
            minWidth: null,
            width: null,
            modal: false,
            timeout: null,
            autoOpen: false,
            createTitleBar: false,
            autoPosition: false,
            autoSize: false,
            draggable: false,
            resizable: false,
            buttons: [
                {
                    'class': "action close",
                    text: "close",
                    click: function () {
                        $(this).dropdownDialog("close");
                    }
                }
            ]
        },
        /**
         * extend default functionality to bind the opener for dropdown
         * @private
         */
        _create: function() {
            this._super();
            this.uiDialog.addClass(this.options.defaultDialogClass);
            var _self = this;
            if(_self.options.triggerTarget) {
                $(_self.options.triggerTarget).on(_self.options.triggerEvent,function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if(!_self._isOpen) {
                        $('.' + _self.options.defaultDialogClass + ' > .ui-dialog-content').dropdownDialog("close");
                        _self.open();
                    }
                    else {
                        _self.close(event);
                    }
                });
            }

        },

        /**
         * extend default functionality to close the dropdown  with custom delay on mouse out and also to close when clicking outside
         */
        open: function () {
            this._super();
            var _self = this;
            if(_self.options.dialogContentClass) {
                _self.element.addClass(_self.options.dialogContentClass);
            }
            if(_self.options.closeOnMouseLeave) {

                this._mouseEnter(_self.uiDialog);
                this._mouseLeave(_self.uiDialog);
                if(_self.options.triggerTarget) {
                    this._mouseLeave($(_self.options.triggerTarget));
                }
            }

            if(_self.options.closeOnClickOutside) {
                $('body').on('click.outsideDropdown', function (event) {
                    if(_self._isOpen && !$(event.target).closest('.ui-dialog').length) {
                        if (timer) {
                            clearTimeout(timer);
                        }
                        _self.close(event);
                        }
                    }
                );
            }
            // adding the class on the opener and parent element for dropdown
            if(_self.options.triggerClass) {
                $(_self.options.triggerTarget).addClass(_self.options.triggerClass);
            }
            if(_self.options.parentClass) {
                $(_self.options.appendTo).addClass(_self.options.parentClass);
            }
        },

        /**
         * extend default functionality to reset the timer and remove the active class for opener
         * @param event
         */
        close: function(event) {
            this._super();
            if(this.options.dialogContentClass) {
                this.element.removeClass(this.options.dialogContentClass);
            }
            if(this.options.triggerClass) {
                $(this.options.triggerTarget).removeClass(this.options.triggerClass);
            }
            if(this.options.parentClass) {
                $(this.options.appendTo).removeClass(this.options.parentClass);
            }
            if(timer) {
                clearTimeout(timer);
            }
            if(this.options.triggerTarget) {
                $(this.options.triggerTarget).off("mouseleave");
            }
            this.uiDialog.off("mouseenter");
            this.uiDialog.off("mouseleave");
            $('body').off('click.outsideDropdown');
        },

        _position: function() {
            if(this.options.autoPosition) {
                this._super();
            }
        },
        _createTitlebar: function() {
            if(this.options.createTitleBar) {
                this._super();
            }
            else {
                // the title bar close button is referenced in _focusTabbable function, so to prevent errors it must be declared
                this.uiDialogTitlebarClose = $("<div>");
            }
        },

        _size: function() {
            if(this.options.autoSize) {
                this._super();
            }
        },

        _mouseLeave : function(handler) {
            var _self = this;
            handler.on("mouseleave", function (event) {
                event.stopPropagation();
                if (_self._isOpen) {
                    if (timer) {
                        clearTimeout(timer);
                    }
                    timer = setTimeout(function (event) {
                        _self.close(event);
                    }, _self.options.timeout);
                }
            });
        },

        _mouseEnter : function(handler){
            handler.on("mouseenter", function (event) {
                event.stopPropagation();
                if (timer) {
                    clearTimeout(timer);
                }
            });
        },

        _setOption: function( key, value ) {
            this._super(key, value);
            if ( key === "triggerTarget" ) {
                this.options.triggerTarget = value;
            }
        }
    });

    return $.mage.dropdownDialog;
});
