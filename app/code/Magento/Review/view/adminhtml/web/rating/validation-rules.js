/**
 * Rating validation rules
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "mage/validation"
], function($){

    $.validator.addMethod(
        'validate-rating',
        function () {
            var ratings = $('#detailed_rating').find('.field-rating'),
                noError = true;

            ratings.each(function (index, rating) {
                noError = noError && $(rating).find('input:checked').length > 0;
            });
            return noError;
        },
        'Please select one of each ratings above.');

});