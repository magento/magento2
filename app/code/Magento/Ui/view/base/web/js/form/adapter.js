/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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