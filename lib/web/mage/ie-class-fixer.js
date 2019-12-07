/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable strict */
(function () {
    var userAgent = navigator.userAgent, // user agent identifier
        html = document.documentElement, // html tag
        gap = ''; // gap between classes

    if (html.className) { // check if neighbour class exist in html tag
        gap = ' ';
    } // end if

    if (userAgent.match(/Trident.*rv[ :]*11\./)) { // Special case for IE11
        html.className += gap + 'ie11';
    } // end if

})();
