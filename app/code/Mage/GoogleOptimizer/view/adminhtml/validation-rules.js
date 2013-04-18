/**
 * GoogleOptimizer client side validation rules
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
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
(function ($) {
    $.validator.addMethod('validate-googleoptimizer', function(v,element) {
        var fieldEntry = false,
            self = false,
            validationResult = true,
            elements = $('.validate-googleoptimizer');

        if (elements.length) {
            elements.each(function() {
                var elm = $(this);
                if ((elm.val() != "none") && (elm.val() != null) && (elm.val().length != 0)) {
                    fieldEntry = true;
                    if (elm.prop('id') == element.id) {
                        self = true;
                    }
                } else {
                    validationResult = false;
                }
            });
        }
        if (fieldEntry && !validationResult && !self) {
            return false;
        }
        return true;
    }, 'This is a required field unless all the fields are empty.');
    $.validator.addMethod('validate-googleoptimizer-attributes', function(v,element) {
        return googleOptimizerCheckAttributesCount(element);
    }, 'Not more than 8 attributes allowed.');
})(jQuery);
