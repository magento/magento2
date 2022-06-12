/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    function addPassiveListeners(jQuery) {
        jQuery.event.special.touchstart = {
            setup: function( _, ns, handle ) {
                this.addEventListener("touchstart", handle, { passive: !ns.includes("noPreventDefault") });
            }
        };

        jQuery.event.special.touchmove = {
            setup: function( _, ns, handle ) {
                this.addEventListener("touchmove", handle, { passive: !ns.includes("noPreventDefault") });
            }
        };

        jQuery.event.special.wheel = {
            setup: function( _, ns, handle ){
                this.addEventListener("wheel", handle, { passive: true });
            }
        };

        jQuery.event.special.mousewheel = {
            setup: function( _, ns, handle ){
                this.addEventListener("mousewheel", handle, { passive: true });
            }
        };
    }

    return function ($) {
        addPassiveListeners($); /* patch for jquery 3.6.0 */

        return $;
    };
});
