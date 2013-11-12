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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    "use strict";
    $.widget('mage.integration', {
        options: {
            authType: null, // Auth type : Manual or Oauth
            formSelector: null,
            endpointIdSelector: null,
            endpointContainerClassSelector: null,
            requiredClass: 'required-entry'
        },

        /**
         * Initialize integration widget
         * @private
         */
        _init: function() {
            this._toggleEndpoint();
        },

        /**
         * Bind a click handler to the widget's context element.
         * @private
         */
        _create: function() {
            this._on({
                change: '_toggleEndpoint'
            });
            this._on($(this.options.formSelector), {
                submit: '_resetEndpoint'
            });
        },

        /**
         * Toggle the visibility of the endpoint field based on Auth thype selected
         * @private
         */
        _toggleEndpoint: function() {
            var isOauth =  parseInt(this.element.val()) === this.options.authType;
            $(this.options.endpointContainerClassSelector).children().toggle(isOauth);
            $(this.options.endpointIdSelector).toggleClass(this.options.requiredClass, isOauth);
        },

        /**
         * Reset endpoint field if the Authentication type is not Oauth
         *
         * @private
         */
        _resetEndpoint: function() {
            if (parseInt(this.element.val()) !== this.options.authType) {
                $(this.options.endpointIdSelector).val('');
            }
        }
    });
})(jQuery);