/**
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
 * @category    cart
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function($) {
    $.extend(true, $, {
        mage: {
            decorator: (function() {
                /**
                 * Decorate a list (e.g. a <ul> containing <li>) recursively if specified.
                 * @param {string} list
                 * @param {boolean} isRecursive
                 */
                this.list = function (list, isRecursive) {
                    var items;
                    if ($(list).length > 0) {
                        if (isRecursive) {
                            items = $(list).children();
                        } else {
                            items = $(list).find('li');
                        }
                        this.general(items, ['odd', 'even', 'last']);
                    }
                };

                /**
                 * Annotate a set of DOM elements with decorator classes.
                 * @param {Object} elements
                 * @param {array} decoratorParams
                 */
                this.general = function (elements, decoratorParams) {
                    var allSupportedParams = {
                        even: 'odd', // Flip jQuery odd/even so that index 0 is odd.
                        odd: 'even',
                        last: 'last',
                        first: 'first'
                    };

                    decoratorParams = decoratorParams || allSupportedParams;

                    if (elements) {
                        $.each(decoratorParams, function (index, param) {
                            if (param === 'even' || param === 'odd') {
                                elements.filter(':' + param).removeClass('odd even').addClass(allSupportedParams[param]);
                            } else {
                                elements.filter(':' + param).addClass(allSupportedParams[param]);
                            }
                        });
                    }
                };

                return this;
            }())
        }
    });
})(jQuery);
