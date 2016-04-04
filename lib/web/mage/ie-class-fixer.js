/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
