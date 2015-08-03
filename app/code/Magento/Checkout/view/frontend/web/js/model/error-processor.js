/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'mage/url',
        'Magento_Ui/js/model/messageList'
    ],
    function (url, messageList) {
        'use strict';

        return {
            process: function (response) {
                if (response.status == 401) {
                    window.location.replace(url.build('customer/account/login/'));
                } else {
                    var error = JSON.parse(response.responseText);
                    messageList.addErrorMessage(error);
                }
            }
        };
    }
);
