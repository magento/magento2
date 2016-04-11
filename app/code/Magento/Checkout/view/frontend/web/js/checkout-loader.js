/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jslint browser: true*/
(function () {
    'use strict';

    var checkInterval;

    checkInterval = setInterval(function () {
        var checkoutContainer = document.getElementById('checkoutSteps'),
            steps,
            loaderContainer;

        //Return if checkout steps container not loaded
        if (!checkoutContainer) {
            return;
        }

        //Checkout steps
        steps = checkoutContainer.getElementsByTagName('li');

        //Remove loader and clear update interval if content loaded
        if (steps && steps.length > 0) {
            clearInterval(checkInterval);
            loaderContainer = document.getElementById('checkout-loader');

            if (loaderContainer && loaderContainer.parentNode) {
                loaderContainer.parentNode.removeChild(loaderContainer);
            }
        }

    }, 100);
})();
