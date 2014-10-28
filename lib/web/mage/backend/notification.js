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
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){

    $.widget("mage.notification", {
        options: {
            templates: {
                global: '<div class="messages"><div class="message {{if error}}error{{/if}}"><div>${message}</div></div></div>'
            }
        },

        /**
         * Notification creation
         * @protected
         */
        _create: function() {
            $.each(this.options.templates, function(key, template) {
                $.template(key + 'Notification', template);
            });
            $(document).on('ajaxComplete ajaxError', $.proxy(this._add, this));
        },

        /**
         * Add new message
         * @protected
         * @param {Object} event object
         * @param {Object} jqXHR The jQuery XMLHttpRequest object returned by $.ajax()
         * @param {Object}
         */
        _add: function(event, jqXHR) {
            try {
                var response = $.parseJSON(jqXHR.responseText);
                if (response && response.error && response.html_message) {
                    this.element.find('[data-container-for=messages]').html(response.html_message);
                }
            } catch(e) {}
        }
    });

});
