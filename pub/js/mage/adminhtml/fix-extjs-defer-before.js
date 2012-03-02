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
 * @package     js
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*@cc_on
// code only for IE7 when ExtJs overwrite "defer" function in PrototypeJs
(function(){
    var last = null;
    var ie7 = @if(@_jscript_version==5.7) 1 @end + 0;
    if (ie7) {
        var eDefer = Function.prototype.defer;
        Function.prototype.defer = function() {
            // prevent throw stack overflow exception
            if (last !== this) {
                last = this;
                eDefer.apply(last, arguments);
            }
            return this;
        };
    }
})();
@*/
