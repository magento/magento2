/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {
        /**
         * Implementation of zIndex used from jQuery UI
         * @param {Element} elem
         * @private
         */
        getValue: function (elem) {
            var position, zIndex;

            /* eslint-disable max-depth */
            while (elem.length && elem[0] !== document) {
                // Ignore z-index if position is set to a value where z-index is ignored by the browser
                // This makes behavior of this function consistent across browsers
                // WebKit always returns auto if the element is positioned
                position = elem.css('position');

                if (position === 'absolute' || position === 'relative' || position === 'fixed') {
                    // IE returns 0 when zIndex is not specified
                    // other browsers return a string
                    // we ignore the case of nested elements with an explicit value of 0
                    zIndex = parseInt(elem.css('zIndex'), 10);

                    if (!isNaN(zIndex) && zIndex !== 0) {
                        return zIndex;
                    }
                }
                elem = elem.parent();
            }

            return 0;
            /* eslint-enable max-depth */
        }
    }
});
