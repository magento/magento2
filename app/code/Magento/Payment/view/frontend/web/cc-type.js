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
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.creditCardType', {
        options: {
            typeCodes: ['SS', 'SM', 'SO'] // Type codes for Switch/Maestro/Solo credit cards.
        },

        /**
         * Bind change handler to select element and trigger the event to show/hide
         * the Switch/Maestro or Solo credit card type container for those credit card types.
         * @private
         */
        _create: function() {
            this.element.on('change', $.proxy(this._toggleCardType, this)).trigger('change');
        },

        /**
         * Toggle the Switch/Maestro and Solo credit card type container depending on which
         * credit card type is selected.
         * @private
         */
        _toggleCardType: function() {
            $(this.options.creditCardTypeContainer)
                .toggle($.inArray(this.element.val(), this.options.typeCodes) !== -1);
        }
    });

});