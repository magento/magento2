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
/*jshint browser:true jquery:true */
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    /**
     * This widget will allow a control with the fieldsetResetControl widget attached to reset a set of input fields.
     * The input fields to reset are defined by the inputSelector selector. The widget will store a clone of the fields
     * on create, and on trigger of fieldsetReset event it resets the defined fields. The event is triggered by the
     * reset control widget.
     *
     * For inputs of type file, the whole dom element is replaced as changing the value is a security violation
     * For inputs of type checkbox or radio, the checked attribute is added or removed as appropriate
     * For all others the jquery .val method is used to update to value to the original.
     */
    $.widget('mage.fieldsetControls', {
        original: undefined,
        options: {
            inputSelector: '[data-reset="true"]'
        },
        _create: function() {
            this.original = this.element.find(this.options.inputSelector).clone(true);
            this._bind();
        },
        _bind: function() {
            this._on({
                'fieldsetReset': '_onReset'
            });
        },
        _onReset: function(e) {
            e.stopPropagation();
            // find all the ones we have to remove
            var items = this.element.find(this.options.inputSelector);
            // loop over replacing each one.
            items.each($.proxy(function(index, item) {
                if ($(item).attr('type') == 'file') {
                    // Replace the current one we found with a clone of the original saved earlier
                    $(item).replaceWith($(this.original[index]).clone(true));
                }
                else if ($(item).attr('type') == 'checkbox' || $(item).attr('type') == 'radio') {
                    // Return to original state.
                    if ($(this.original[index]).attr('checked') === undefined) {
                        $(item).removeAttr('checked');
                    }
                    else {
                        $(item).attr('checked',$(this.original[index]).attr('checked'));
                    }
                }
                else {
                    // Replace the value with the original
                    $(item).val($(this.original[index]).val());
                }
            }, this));
        }
    });
    
    $.widget('mage.fieldsetResetControl', {
        _create: function() {
            this._bind();
        },
        _bind: function() {
            this._on({
                click: '_onClick'
            });
        },
        _onClick: function(e) {
            e.stopPropagation();
            $(this.element).trigger('fieldsetReset');
        }
    });

});
