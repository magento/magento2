/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/modal/confirm'
], function (confirm) {
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
        confirm({
            content: message,
            actions: {
                confirm: function () {
                    setLocation(url);
                }
            }
        });

        return false;
    };

    /**
     * Helper for onclick action.
     * @param {String} message
     * @param {String} url
     * @returns {boolean}
     */
    window.confirmSetLocation = window.deleteConfirm;
});
