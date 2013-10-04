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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($, window) {
    $.widget('mage.compareList', {
        _create: function() {
            this.element.decorate('table');

            $(this.options.windowCloseSelector).on('click', function() {
                window.close();
            });

            $(this.options.windowPrintSelector).on('click', function(e) {
                e.preventDefault();
                window.print();
            });

            var ajaxSpinner = $(this.options.ajaxSpinner);
            $(this.options.productRemoveSelector).on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(e.target).data('url'),
                    type: 'POST',
                    beforeSend: function() {
                        ajaxSpinner.show();
                    }
                }).done(function() {
                    ajaxSpinner.hide();
                    window.location.reload();
                    window.opener.location.reload();
                });
            });

            $.each(this.options.selectors, function(i, selector) {
                $(selector).on('click', function(e) {
                    e.preventDefault();
                    window.opener.focus();
                    window.opener.location.href = $(this).data('url');
                });
            });
        }
    });
})(jQuery, window);
