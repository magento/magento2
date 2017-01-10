/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';

        var isInAction = ko.observable(false);

        return {
            isInAction: isInAction,
            stopEventPropagation: function (event) {
                event.stopImmediatePropagation();
                event.preventDefault();
            }
        };
    }
);
