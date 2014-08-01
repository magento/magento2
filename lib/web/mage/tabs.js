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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    "jquery",
    "jquery/ui",
    "mage/collapsible"
], function($){
    "use strict";
        
    $.widget("mage.tabs", {
        options: {
            active: 0,
            disabled: [],
            openOnFocus: true,
            collapsible: false,
            collapsibleElement: "[data-role=collapsible]",
            header: "[data-role=title]",
            content: "[data-role=content]",
            trigger: "[data-role=trigger]",
            closedState: null,
            openedState: null,
            disabledState: null,
            ajaxUrlElement: "[data-ajax=true]",
            ajaxContent: false,
            loadingClass: null,
            saveState: false,
            animate: false,
            icons: {
                activeHeader: null,
                header: null
            }
        },

        _create : function () {
            if((typeof this.options.disabled) === "string") {
                this.options.disabled = this.options.disabled.split(" ").map(function(item) {
                    return parseInt(item, 10);
                });
            }
            this._processPanels();

            this._handleDeepLinking();

            this._processTabIndex();

            this._closeOthers();

            this._bind();
        },

        _destroy: function() {
            $.each(this.collapsibles, function() {
                $(this).collapsible("destroy");
            });
        },

        /**
         * If deep linking is used, all sections must be closed but the one that contains the anchor.
         * @private
         */
        _handleDeepLinking: function() {
            var self = this,
                anchor = window.location.hash,
                anchorId = anchor.replace("#","");
            if (anchor) {
                $.each(self.contents,function(i) {
                    if($(this).attr("id") === anchorId || $(this).find(anchor).length > 0) {
                        self.collapsibles.not(self.collapsibles.eq(i)).collapsible("forceDeactivate");
                        return false;
                    }
                });
            }
        },

        /**
         * When the widget gets instantiated, the first tab that is not disabled receive focusable property
         * When a tabs gets expanded receive tabIndex 0 and the others -1
         * @private
         */
        _processTabIndex: function() {
            var self = this;
            self.triggers.attr("tabIndex",-1);
            $.each(this.collapsibles, function(i) {                
                if(!$(this).collapsible("option","disabled")) {
                    self.triggers.eq(i).attr("tabIndex", 0);
                    return false;
                }
            });
            $.each(this.collapsibles, function(i) {
                $(this).on("beforeOpen", function () {
                    self.triggers.attr("tabIndex",-1);
                    self.triggers.eq(i).attr("tabIndex",0);

                });
            });
        },

        /**
         * Prepare the elements for instantiating the collapsible widget
         * @private
         */
        _processPanels: function() {
            this.collapsibles = this.element.find(this.options.collapsibleElement);
            this.headers = this.element.find(this.options.header);
            if (this.headers.length === 0) {
                this.headers = this.collapsibles;
            }
            this.contents = this.element.find(this.options.content);
            this.triggers = this.element.find(this.options.trigger);
            if (this.triggers.length === 0) {
                this.triggers = this.headers;
            }
            this._callCollapsible();
        },

        /**
         * Setting the disabled and active tabs and calling instantiation of collapsible
         * @private
         */
        _callCollapsible: function() {
            var self = this,
                disabled = false,
                active = false;

            $.each(this.collapsibles, function(i) {
                disabled = active = false;
                if($.inArray(i,self.options.disabled) !== -1) {
                    disabled = true;
                }
                if(i === self.options.active) {
                    active = true;
                }
                self._instantiateCollapsible(this,i,active,disabled);
            });
        },

        /**
         * Instantiate collapsible
         * @param element
         * @param index
         * @param active
         * @param disabled
         * @private
         */
        _instantiateCollapsible: function(element,index,active,disabled) {
            $(element).collapsible(
                $.extend({}, this.options, {
                    active: active,
                    disabled: disabled,
                    header: this.headers.eq(index),
                    content: this.contents.eq(index),
                    trigger: this.triggers.eq(index)}
                ));
        },

        /**
         * Adding callback to close others tabs when one gets opened
         * @private
         */
        _closeOthers: function() {
            var self = this;
            $.each(this.collapsibles, function() {
                $(this).on("beforeOpen", function () {
                    self.collapsibles.not(this).collapsible("forceDeactivate");
                });
            });
        },

        activate: function(index) {
            this._toggleActivate("activate",index);
        },

        deactivate: function(index) {
            this._toggleActivate("deactivate",index);
        },

        _toggleActivate: function(action,index) {
            this.collapsibles.eq(index).collapsible(action);
        },

        disable: function(index) {
            this._toggleEnable("disable",index);
        },

        enable: function(index) {
            this._toggleEnable("enable",index);
        },

        _toggleEnable: function(action,index) {
            if($.isArray( index )) {
                var self = this;
                $.each(index, function() {
                    self.collapsibles.eq(this).collapsible(action);
                });
            } else if(index === undefined) {
                this.collapsibles.collapsible(action);
            } else {
                this.collapsibles.eq(index).collapsible(action);
            }
        },

        _keydown: function( event ) {
            if ( event.altKey || event.ctrlKey ) {
                return;
            }
            var keyCode = $.ui.keyCode,
                toFocus = false,
                toFocusIndex,
                enabledTriggers = [],
                length,
                currentIndex,
                self = this;

            $.each(this.triggers, function() {
                if(!self.collapsibles.eq(self.triggers.index($(this))).collapsible("option","disabled")) {
                    enabledTriggers.push(this);
                }
            });
            length = $(enabledTriggers).length;
            currentIndex = $(enabledTriggers).index(event.target);
            var nextToFocus = function(direction) {
                if(length > 0) {
                    if(direction === "right") {
                        toFocusIndex = (currentIndex + 1) % length;
                    } else {
                        toFocusIndex = (currentIndex + length - 1) % length;
                    }
                    return enabledTriggers[toFocusIndex];
                }
                else return event.target;
            };

            switch ( event.keyCode ) {
                case keyCode.RIGHT:
                case keyCode.DOWN:
                    toFocus = nextToFocus("right");
                    break;
                case keyCode.LEFT:
                case keyCode.UP:
                    toFocus = nextToFocus("left");
                    break;
                case keyCode.HOME:
                    toFocus = enabledTriggers[0];
                    break;
                case keyCode.END:
                    toFocus = enabledTriggers[length - 1];
                    break;
            }

            if ( toFocus ) {
                toFocusIndex = this.triggers.index(toFocus);
                $( event.target ).attr( "tabIndex", -1 );
                $( toFocus ).attr( "tabIndex", 0 );
                toFocus.focus();
                if(this.options.openOnFocus) {
                    this.activate(toFocusIndex);
                }
                event.preventDefault();
            }
        },

        _bind: function () {
            var events = {
                keydown: "_keydown"
            };
            this._off(this.triggers);
            this._on(this.triggers, events);
        }
    });
});
