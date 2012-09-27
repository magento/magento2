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
 * @category    frontend home menu
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true */
(function ($) {
    // Default fields to initialize for menu
    var menuInit = {
        showDelay: 100,
        hideDelay: 100,
        menuSelector: '#nav .parent'
    };

    function show(subElement) {
        if (subElement.data('hideTimeId')) {
            clearTimeout(subElement.data('hideTimeId'));
        }
        subElement.data('showTimeId', setTimeout(function () {
            if (!subElement.hasClass('shown-sub')) {
                subElement.addClass('shown-sub');
            }
        }, menuInit.showDelay));
    }

    function hide(subElement) {
        if (subElement.data('showTimeId')) {
            clearTimeout(subElement.data('showTimeId'));
        }
        subElement.data('hideTimeId', setTimeout(function () {
            if (subElement.hasClass('shown-sub')) {
                subElement.removeClass('shown-sub');
            }
        }, menuInit.hideDelay));
    }

    $(document).ready(function () {
        // Trigger initalize event
        $.mage.event.trigger("mage.menu.initialize", menuInit);
        $(menuInit.menuSelector).on('mouseover', function () {
            $(this).addClass('over');
            show($(this).children('ul'));
        });
        $(menuInit.menuSelector).on('mouseout', function () {
            $(this).removeClass('over');
            hide($(this).children('ul'));
        });
    });
})(jQuery);