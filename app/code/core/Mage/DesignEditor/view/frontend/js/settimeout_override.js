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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

(function() {

    var bindBeforeUnload = function() {
        window.onbeforeunload = function(e) {
            var e = e || window.event;
            var messageText = 'Automatic redirect has been triggered.';
            // For IE and Firefox
            if (e) {
                e.returnValue = messageText;
            }
            // For Chrome and Safari
            return messageText;
        };
    }

    var unbindBeforeUnload = function () {
        window.onbeforeunload = null;
    }

    window.setTimeout = (function(oldSetTimeout) {
        return function(func, delay) {
            return oldSetTimeout(function() {
                try {
                    bindBeforeUnload();
                    func();
                    unbindBeforeUnload();
                }
                catch (exception) {
                    unbindBeforeUnload();
                    throw exception;
                }
            }, delay);
        };
    })(window.setTimeout);

    window.setInterval = (function(oldSetInterval) {
        return function(func, delay) {
            return oldSetInterval(function() {
                try {
                    bindBeforeUnload();
                    func();
                    unbindBeforeUnload();
                }
                catch (exception) {
                    unbindBeforeUnload();
                    throw exception;
                }
            }, delay);
        };
    })(window.setInterval);

})();
