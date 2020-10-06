/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    return function (config) {
        var previousDefault = $(config.isDefaultRadioSelector + ':checked').val();
        var clearDefault = false;

        config = $.extend({
            'hiddenInputSelector': '',
            'isDefaultRadioSelector': 'input[name="default[]"]',
            'wrapperSelector': '#manage-options-panel'
        }, config);

        if (config.hasOwnProperty('hiddenInputSelector') && $(config.hiddenInputSelector).length) {
            clearDefault = $(config.hiddenInputSelector);
        }

        //Listen for click on radios. Listening on the wrapper accounts for dynamically added rows.
        $(config.wrapperSelector).on(
            'click',
            config.isDefaultRadioSelector,
            function () {
                var radioValue = $(this).val();

                //If radio is already selected, deselect it.
                if (radioValue === previousDefault) {
                    this.checked = false;
                    this.blur();
                    previousDefault = false;
                    if (clearDefault) {
                        clearDefault.val('1');
                    }
                } else {
                    //New radio selected.
                    previousDefault = radioValue;
                    if (clearDefault) {
                        clearDefault.val('0');
                    }
                }
            }
        );
    };
});
