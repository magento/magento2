define([
    'jquery',
    'moment',
    'mageUtils',
    'jquery/validate',
    'validation',
    'mage/translate'
], function ($, moment, utils) {
    'use strict';

    $.validator.addMethod(
        'validate-dob',
        function (value, element, params) {
            var dateFormat = utils.convertToMomentFormat(params.dateFormat);

            if (value === '') {
                return true;
            }

            return moment(value, dateFormat).isBefore(moment());
        },
        $.mage.__('The Date of Birth should not be greater than today.')
    );
});
