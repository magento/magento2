/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.validation', widget, {
            /**
             * Handle form validation. Focus on first invalid form field.
             *
             * @param {jQuery.Event} event
             * @param {Object} validation
             */
            listenFormValidateHandler: function (event, validation) {
                var firstActive = $(validation.errorList[0].element || []),
                    lastActive = $(validation.findLastActive() ||
                        validation.errorList.length && validation.errorList[0].element || []),
                    windowHeight = $(window).height(),
                    parent, successList;

                if (lastActive.is(':hidden')) {
                    parent = lastActive.parent();
                    $('html, body').animate({
                        scrollTop: parent.offset().top - windowHeight / 2
                    });
                }

                // ARIA (removing aria attributes if success)
                successList = validation.successList;

                if (successList.length) {
                    $.each(successList, function () {
                        $(this)
                            .removeAttr('aria-describedby')
                            .removeAttr('aria-invalid');
                    });
                }

                if (firstActive.length) {
                    firstActive.focus();
                }
            }
        });

        return $.mage.validation;
    }
});

