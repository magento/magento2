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
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

(function() {
    var source = [
        '../../jquery/jquery.js',
        '../../mage/terms.js',
        '../../mage/dropdowns.js',
        '../../jquery/jquery.popups.js',
        '../../js/mui.js'
    ];

    for (var i=0, len=source.length; i<len; i++) {
        var script = document.createElement('script');

        script.type = 'text/javascript';
        script.async = false;
        script.src = source[i];

        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(script, s);
    }
})();
