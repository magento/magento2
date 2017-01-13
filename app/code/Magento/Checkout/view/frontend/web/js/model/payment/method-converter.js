/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'underscore'
    ],
    function (_) {
        'use strict';

        return function (methods) {
            _.each(methods, function(method) {
                if (method.hasOwnProperty('code')) {
                    method.method = method.code;
                    delete method.code;
                }
            });

            return methods;
        };
    }
);
