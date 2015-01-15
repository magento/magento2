/**
 * Downloadable client side validation rules
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global newFileContainer:true, alertAlreadyDisplayed:true, alert:true, linkType:true*/
define([
    "jquery",
    "mage/validation",
    "mage/translate"
], function($){

    $.validator.addMethod('validate-downloadable-file', function (v, element) {
        var elmParent = $(element).parent(),
            linkType = elmParent.find('input[value="file"]');
        if (linkType.is(':checked') && (v === '' || v === '[]')) {
            newFileContainer = elmParent.find('.new-file');
            if (!alertAlreadyDisplayed && (newFileContainer.empty() || newFileContainer.is(':visible'))) {
                alertAlreadyDisplayed = true;
                alert($.mage.__('There are files that were selected but not uploaded yet. ' +
                    'Please upload or remove them first')
                );
            }
            return false;
        }
        return true;
    }, 'Please upload a file.');
    $.validator.addMethod('validate-downloadable-url', function (v, element) {
        linkType = $(element).parent().find('input[value="url"]');
        if (linkType.is(':checked') && v === '') {
            return false;
        }
        return true;
    }, 'Please specify Url.');

});