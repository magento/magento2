/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "mage/mage",
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
                isValid = $.mage.isValidSelector(anchor),
                anchorId = anchor.replace("#","");

            if (anchor && isValid) {
                $.each(self.contents,function(i) {
                    if($(this).attr("id") === anchorId) {
                        self.collapsibles.not(self.collapsibles.eq(i)).collapsible("forceDeactivate");
                        return false;
                    }
                });
            }
        },

        /**
         * When the widget gets instantiated, the first tab that is not disabled receive focusable property
         * Updated: for accessibility all tabs receive tabIndex 0
         * @private
         */
        _processTabIndex: function() {
            var self = this;
            self.triggers.attr("tabIndex",0);
            $.each(this.collapsibles, function(i) {
                if(!$(this).collapsible("option","disabled")) {
                    self.triggers.eq(i).attr("tabIndex", 0);
                    return false;
                }
            });
            $.each(this.collapsibles, function(i) {
                $(this).on("beforeOpen", function () {
                    self.triggers.attr("tabIndex",0);
                    self.triggers.eq(i).attr("tabIndex",0);

                });
            });
        },

        /**
         * Prepare the elements for instantiating the collapsible widget
         * @private
         */
        _processPanels: function() {
            this.contents = this.element.find(this.options.content);

            this.collapsibles = this.element.find(this.options.collapsibleElement);

            this.collapsibles
                .attr('role', 'presentation')
                .parent()
                .attr('role', 'tablist');

            this.headers = this.element.find(this.options.header);
            if (this.headers.length === 0) {
                this.headers = this.collapsibles;
            }
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

    return $.mage.tabs;
});
