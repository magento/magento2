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
(function () {

    var userAgent = navigator.userAgent, // user agent identifier
        html = document.documentElement, // html tag
        version = 9, // minimal supported version of IE
        gap = ''; // gap between classes

    if (html.className) { // check if neighbour class exist in html tag
        gap = ' ';
    } // end if

    for (version; version <= 10; version++) { // loop from minimal to 10 version of IE
        if (userAgent.indexOf('MSIE ' + version) > -1) { // match IE individual name
            html.className += gap + 'ie' + version;
        } // end if
    }

    if (userAgent.match(/Trident.*rv[ :]*11\./)) { // Special case for IE11
        html.className += gap + 'ie11';
    } // end if

})();