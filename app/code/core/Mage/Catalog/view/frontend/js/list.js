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
 * @category    mage compare list
 * @package     mage
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/

(function ($) {
    $(document).ready(function () {
        var _compareList = {
            productSelector: null,
            productImageSelector: null,
            productAddToCartSelector: null,
            productWishListSelector: null,
            productRemoveSelector: null,
            productFormSelector: null,
            ajaxSpinner: null,
            windowCloseSelector: null,
            printSelector: null
        };

        $.mage.event.trigger('mage.compare-list.initialize', _compareList);
        $(_compareList.productFormSelector).decorate('table');

        function _setParentWindow(selector) {
            $(selector).on('click', function (e) {
                e.preventDefault();
                window.opener.focus();
                window.opener.location.href = $(this).data('url');
            });
        }

        // Window close
        $(_compareList.windowCloseSelector).on('click', function () {
            window.close();
        });
        // Window print
        $(_compareList.printSelector).on('click', function (e) {
            e.preventDefault();
            window.print();
        });

        $(_compareList.productRemoveSelector).on('click', function (e) {
            e.preventDefault();
            // Send remove item request, after that reload windows
            $.ajax({
                url: $(_compareList.productRemoveSelector).data('url'),
                type: 'POST',
                beforeSend: function () {
                    $(_compareList.ajaxSpinner).show();
                }
            }).done(function () {
                $(_compareList.ajaxSpinner).hide();
                window.location.reload();
                window.opener.location.reload();
            });
        });

        $.each(_compareList, function (index, prop) {
            // Removed properties that doesn't need to call _setParentWindow
            var notAllowedProp = ['windowCloseSelector', 'printSelector', 'productRemoveSelector', 'ajaxSpinner','productFormSelector'];
            if ($.inArray(index, notAllowedProp) === -1) {
                _setParentWindow(prop);
            }
        });
    });
})(jQuery);