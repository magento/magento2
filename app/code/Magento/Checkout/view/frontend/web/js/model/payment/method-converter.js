/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'underscore'
], function (_) {
    'use strict';

    return function (methods) {
        _.each(methods, function (method) {
            if (method.hasOwnProperty('code')) {
                method.method = method.code;
                delete method.code;
            }
        });

        return methods;
    };
});
