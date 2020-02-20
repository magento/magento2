define([
    'jquery',
    'moment',
    'jquery/validate',
    'mage/translate'
], function ($, moment) {
    'use strict';

    $.validator.addMethod(
        'validate-dob',
        function (value) {
            if (value === '') {
                return true;
            }

            return moment(value).isBefore(moment());
        },
        $.mage.__('The Date of Birth should not be greater than today.')
    );
});
