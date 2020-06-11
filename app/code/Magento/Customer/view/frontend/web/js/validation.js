define([
    'jquery',
    'moment',
    'mageUtils',
    'jquery/validate',
    'mage/translate'
], function ($, moment, utils) {
    'use strict';

    $.validator.addMethod(
        'validate-dob',
        function (value) {
            if (value === '') {
                return true;
            }
            var valueFormatted,
                inputFormat = 'd/M/yy';

            valueFormatted = moment(value, utils.convertToMomentFormat(inputFormat));
            return valueFormatted.isBefore(moment());
        },
        $.mage.__('The Date of Birth should not be greater than today.')
    );
});
