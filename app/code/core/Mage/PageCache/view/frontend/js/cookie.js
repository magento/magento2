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
 * @category    PageCache
 * @package     js
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $(document).ready(function () {
        var _data = {
            cookieName: undefined,
            cookieLifetime: undefined,
            cookieExpireAt: null
        };
        $.mage.event.trigger('mage.nocachecookie.initialize', _data);
        if (_data.cookieLifetime > 0) {
            _data.cookieExpireAt = new Date();
            _data.cookieExpireAt.setTime(_data.cookieExpireAt.getTime() + _data.cookieLifetime * 1000);
        }
        $.mage.cookies.set(_data.cookieName, 1, _data.cookieExpireAt);
    });
})(jQuery);
