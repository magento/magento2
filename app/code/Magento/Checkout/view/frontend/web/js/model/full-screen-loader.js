/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'rjsResolver'
], function ($, resolver) {
    'use strict';

    var containerId = '#checkout';

    return {

        /**
         * Start full page loader action
         */
        startLoader: function () {
            $(containerId).trigger('processStart');
        },

        /**
         * Stop full page loader action
         *
         * @param {Boolean} [forceStop]
         */
        stopLoader: function (forceStop) {
            var $elem = $(containerId),
                // eslint-disable-next-line magento-coding-standard-eslint-plugin/jquery-no-bind-unbind
                stop = $elem.trigger.bind($elem, 'processStop');

            forceStop ? stop() : resolver(stop);
        }
    };
});
