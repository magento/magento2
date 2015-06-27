/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    /**
     * Set of a temporary methods used to provide
     * backward compatability with a legacy code.
     */
    window.setLocation = function (url) {
        window.location.href = url;
    };

    /**
     * Helper for onclick action.
     * @param {String} message
     * @param {String} url
     * @returns {boolean}
     */
    window.deleteConfirm = function (message, url) {
        if (confirm(message)) {
            setLocation(url);
        }

        return false;
    };
});
