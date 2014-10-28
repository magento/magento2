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
/*jshint jquery:true browser:true*/
/*global Ajax:true alert:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/backend/form",
    "prototype"
], function($){
    "use strict";

    $.widget("mage.categoryForm", $.mage.form, {
        options: {
            categoryIdSelector : 'input[name="general[id]"]',
            categoryPathSelector : 'input[name="general[path]"]'
        },

        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this._super();
            $('body').on('categoryMove.tree', $.proxy(this.refreshPath, this));
        },

        /**
         * Sending ajax to server to refresh field 'general[path]'
         * @protected
         */
        refreshPath: function() {
            if (!this.element.find(this.options.categoryIdSelector).prop('value')) {
                return false;
            }
            // @TODO delete this prototype functional
            new Ajax.Request(
                this.options.refreshUrl,
                {
                    method:     'POST',
                    evalScripts: true,
                    onSuccess: this._refreshPathSuccess.bind(this)
                }
            );
        },

        /**
         * Refresh field 'general[path]' on ajax success
         * @param {Object} The XMLHttpRequest object returned by ajax
         * @protected
         */
        _refreshPathSuccess: function(transport) {
            if (transport.responseText.isJSON()) {
                var response = transport.responseText.evalJSON();
                if (response.error) {
                    alert(response.message);
                } else {
                    if (this.element.find(this.options.categoryIdSelector).prop('value') === response.id) {
                        this.element.find(this.options.categoryPathSelector)
                            .prop('value', response.path);
                    }
                }
            }
        }
    });

});