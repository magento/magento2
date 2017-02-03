/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore'
], function($, _){
    'use strict';

    var buttons = {
        'reset':            '#reset',
        'save':             "#save",
        'saveAndContinue':  '#save_and_continue'
    };

    function initListener(callback, action){
        var selector    = buttons[action],
            elem        = $(selector)[0];

        if (!elem) {
            return;
        }

        if(elem.onclick){
            elem.onclick = null;
        }

        $(elem).off()
                .on('click', callback);
    }

    return {
        on: function(handlers){
            _.each(handlers, initListener);
        }
    }
});
