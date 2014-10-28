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
    "jquery/ui"
], function($){
    'use strict';

    // mage.accordion base functionality
    $.widget('mage.accordion', $.ui.accordion, {
        options: {
            heightStyle: 'content',
            animate: false,
            beforeActivate: function(e, ui) {
                // Make sure sections below current are not clickable and sections above are clickable
                var newPanelParent = $(ui.newPanel).parent();
                if (!newPanelParent.hasClass('allow')) {
                    return false;
                }
                newPanelParent.addClass('active allow').prevAll().addClass('allow');
                newPanelParent.nextAll().removeClass('allow');
                $(ui.oldPanel).parent().removeClass('active');
            }
        },

        /**
         * Accordion creation
         * @protected
         */
        _create: function() {
            // Custom to enable section
            this.element.on('enableSection', function(event, data) {
                $(data.selector).addClass('allow').find('h2').trigger('click');
            });
            this._super();
            $(this.options.activeSelector).addClass('allow active').find('h2').trigger('click');
        }
    });

});