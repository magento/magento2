/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global setLocation */
define([
    'Magento_Ui/js/modal/confirm',
    'mage/dataPost'
], function (confirm, dataPost) {
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
     * @param {Object} postData
     * @returns {Boolean}
     */
    window.deleteConfirm = function (message, url, postData) {
        confirm({
            content: message,
            actions: {
                /**
                 * Confirm action.
                 */
                confirm: function () {
                    if (postData !== undefined) {
                        postData.action = url;
                        dataPost().postData(postData);
                    } else {
                        setLocation(url);
                    }
                }
            }
        });

        return false;
    };

    /**
     * Helper for onclick action.
     * @param {String} message
     * @param {String} url
     * @returns {Boolean}
     */
    window.confirmSetLocation = window.deleteConfirm;
});
