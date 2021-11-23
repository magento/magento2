/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
                stop = $elem.trigger.bind($elem, 'processStop'); //eslint-disable-line jquery-no-bind-unbind

            forceStop ? stop() : resolver(stop);
        }
    };
});
