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
    "jquery"
], function($){

    'use strict';
    var ESC_KEY_CODE = '27';

    $(document)
        .on('click.dropdown', function(event) {
            if (!$(event.target).is('[data-toggle=dropdown].active, ' +
                '[data-toggle=dropdown].active *, ' +
                '[data-toggle=dropdown].active + .dropdown-menu, ' +
                '[data-toggle=dropdown].active + .dropdown-menu *,' +
                '[data-toggle=dropdown].active + [data-target="dropdown"],' +
                '[data-toggle=dropdown].active + [data-target="dropdown"] *')
            ) {
                $('[data-toggle=dropdown].active').trigger('close.dropdown');
            }
        })
        .on('keyup.dropdown', function(event) {
            if (event.keyCode == ESC_KEY_CODE) {
                $('[data-toggle=dropdown].active').trigger('close.dropdown');
            }
        });

    $.fn.dropdown = function(options) {
        options = $.extend({
            parent: null,
            btnArrow: '.arrow',
            activeClass: 'active'
        }, options);

        return this.each(function() {
            var elem = $(this);

            elem.off('open.dropdown, close.dropdown, click.dropdown');
            elem.on('open.dropdown', function() {
                elem
                    .addClass(options.activeClass)
                    .parent()
                    .addClass(options.activeClass);
                elem.find(options.btnArrow).text('\u25b2'); // arrow up
            });

            elem.on('close.dropdown', function() {
                elem
                    .removeClass(options.activeClass)
                    .parent()
                    .removeClass(options.activeClass);
                elem.find(options.btnArrow).text('\u25bc'); // arrow down
            });

            elem.on('click.dropdown', function() {
                var isActive = elem.hasClass('active');
                $('[data-toggle=dropdown].active').trigger('close.dropdown');
                elem.trigger(isActive ? 'close.dropdown' : 'open.dropdown');
                return false;
            });
        });
    };

    $(document).ready(function() {
        $('[data-toggle=dropdown]').dropdown();
    });

});
