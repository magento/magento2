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
    "mage/tabs"
], function($){
    "use strict";

    $.widget("mage.accordion", $.mage.tabs, {
        options: {
            active : [0],
            multipleCollapsible: false,
            openOnFocus: false
        },

        _callCollapsible: function() {
            if((typeof this.options.active) === "string") {
                this.options.active = this.options.active.split(" ").map(function(item) {
                    return parseInt(item, 10);
                });
            }
            var self = this,
                disabled = false,
                active = false;

            $.each(this.collapsibles, function(i) {
                disabled = active = false;
                if($.inArray(i,self.options.disabled) !== -1) {
                    disabled = true;
                }
                if($.inArray(i,self.options.active) !== -1) {
                    active = true;
                }
                self._instantiateCollapsible(this,i,active,disabled);
            });
        },

        /**
         * Overwrites default functionality to provide the option to activate/deactivate multiple sections simultaneous
         * @param action
         * @param index
         * @private
         */
        _toggleActivate: function(action,index) {
            if($.isArray( index && this.options.multipleCollapsible)) {
                var self = this;
                $.each(index, function() {
                    self.collapsibles.eq(this).collapsible(action);
                });
            } else if( (index === undefined) && this.options.multipleCollapsible) {
                this.collapsibles.collapsible(action);
            } else {
                this._super(action,index);
            }
        },

        /**
         * If the Accordion allows multiple section to be active at the same time, if deep linking is used
         * sections that don't contain the id from anchor shouldn't be closed, otherwise the accordion uses the
         * tabs behavior
         * @private
         */
        _handleDeepLinking: function() {
            if(!this.options.multipleCollapsible) {
                this._super();
            }
        },

        /**
         * Prevent default behavior that closes the other sections when one gets activated if the Accordion allows
         * multiple sections simultaneous
         * @private
         */
        _closeOthers: function() {
            if(!this.options.multipleCollapsible) {
                this._super();
            }
            $.each(this.collapsibles, function() {
                $(this).on("beforeOpen", function() {
                    var section = $(this);
                    section.addClass('allow').prevAll().addClass('allow');
                    section.nextAll().removeClass('allow');
                });
            });
        }
    });
});
