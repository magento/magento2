/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define(['underscore'], function (_) {
    'use strict';

    return function (solution, message, argument) {
        var isConfirm = false;

        _.every(argument, function (name) {
            if (solution.solutionsElements[name] &&
                solution.solutionsElements[name].find(solution.enableButton).val() == 1 //eslint-disable-line eqeqeq
            ) {
                isConfirm = true;

                return !isConfirm;
            }

            return !isConfirm;
        }, this);

        if (isConfirm) {
            return confirm(message); //eslint-disable-line no-alert
        }

        return true;
    };
});
