/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

(function() {
    var checkInterval = setInterval(function() {
        var checkoutContainer = document.getElementById('checkoutSteps'),
            childNodes,
            contentLoaded = false;

        //Return if checkout steps container not loaded
        if (!checkoutContainer) {
            return;
        }
        childNodes = checkoutContainer.childNodes;

        //Find checkout steps in page body
        for (var i = 0; i <= childNodes.length; i++) {
            if (typeof childNodes[i] != "undefined"
                && childNodes[i].nodeName == 'LI'
            ) {
                contentLoaded = true;
                break;
            }
        }

        //Remove loader and clear update interval if content loaded
        if (contentLoaded) {
            clearInterval(checkInterval);
            var element = document.getElementById('checkout-loader');
            if (element && element.parentNode) {
                element.parentNode.removeChild(element);
            }
        }

    }, 100);
})();
