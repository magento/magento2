/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define(['ko'], function (ko) {
    'use strict';

    var isInAction = ko.observable(false);

    return {
        isInAction: isInAction,

        /**
         * @param {jQuery.Event} event
         */
        stopEventPropagation: function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
        }
    };
});
