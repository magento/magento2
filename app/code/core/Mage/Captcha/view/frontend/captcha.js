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
 * @category    mage
 * @package     captcha
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($, undefined) {
    $.widget('mage.captcha', {
        options: {
            refreshClass: 'refreshing'
        },
        _create: function () {
            this.element.on('click', $.proxy(this.refresh, this));
        },
        refresh: function () {
            this.element.addClass(this.options.refreshClass);
            $.ajax({
                url: this.options.url,
                type: 'post',
                dataType: 'json',
                context: this,
                data: {'formId': this.options.formSelector.replace(/^(#|.)/, "")},
                success: function (response) {
                    if (response.imgSrc) {
                        $(this.options.formSelector).attr('src', response.imgSrc);
                    }
                    this.element.removeClass(this.options.refreshClass);
                },
                error: function () {
                    this.element.removeClass(this.options.refreshClass);
                }
            });
        }
    });
})(jQuery);

