/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (environment) {
        var deferred = $.Deferred(),
            dependency = 'cardinaljs';

        if (environment === 'sandbox') {
            dependency = 'cardinaljsSandbox';
        }

        require(
            [dependency],
            function (Cardinal) {
                deferred.resolve(Cardinal);
            },
            deferred.reject
        );

        return deferred.promise();
    };
});
