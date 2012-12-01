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
 * @category    mage product view
 * @package     mage
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $(document).ready(function () {

        var topCartInit = {
            // Default values
            intervalDuration: 4000,
            // Filled in initialization event
            container: null,
            closeButton: null
        };

        $.mage.event.trigger('mage.checkout.initialize', topCartInit);

        topCartInit.container = $(topCartInit.container);
        topCartInit.closeButton = $(topCartInit.closeButton);

        var topCartSettings = {
            element: topCartInit.container.parent(),
            elementHeader: topCartInit.container.prev(),
            interval: null
        };

        topCartInit.closeButton.on('click', function () {
            topCartInit.container.slideUp('slow', function () {
                clearTimeout(topCartInit.interval);
            });
        });

        topCartSettings.element.on('mouseleave',function () {
            topCartInit.interval = setTimeout(function () {
                topCartInit.closeButton.trigger('click');
            }, topCartInit.intervalDuration);
        }).on('mouseenter', function () {
            clearTimeout(topCartSettings.interval);
        });

        topCartSettings.elementHeader.on('click', function () {
            $(topCartInit.container).slideToggle('slow');
        });

    });
})(jQuery);