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
    
    $.widget('mage.truncateOptions', {
        options: {
            detailsLink: 'a.details',
            mouseEvents: 'mouseover mouseout',
            truncatedFullValue: 'div.truncated.full.value'
        },

        /**
         * Establish the event handler for mouse events on the appropriate elements.
         * @private
         */
        _create: function() {
            this.element.on(this.options.mouseEvents, $.proxy(this._toggleShow, this))
                .find(this.options.detailsLink).on(this.options.mouseEvents, $.proxy(this._toggleShow, this));
        },

        /**
         * Toggle the "show" class on the associated element.
         * @private
         * @param event {Object} - Mouse over/out event.
         */
        _toggleShow: function(event) {
            $(event.currentTarget).find(this.options.truncatedFullValue).toggleClass('show');
        }
    });

});