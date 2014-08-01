/**
 * Downloadable client side validation rules
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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