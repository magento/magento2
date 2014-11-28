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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    'ko',
    'underscore'
], function(ko, _) {
    'use strict';

    ko.bindingHandlers['class'] = {
        /**
         * @param {HTMLElement} element - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        update: function (element, valueAccessor) {
            var currentValue = ko.utils.unwrapObservable(valueAccessor()),
                prevValue = element['__ko__previousClassValue__'],

                /**
                 * Helper for setting classes11
                 * @param {Array|Object|String} singleValueOrArrayOrObject
                 * @param {Boolean} shouldHaveClass
                 */
                addOrRemoveClasses = function addOrRemoveClassesFn (singleValueOrArrayOrObject, shouldHaveClass) {
                    if (_.isArray(singleValueOrArrayOrObject)) {
                        ko.utils.arrayForEach(singleValueOrArrayOrObject, function (cssClass) {
                            var value = ko.utils.unwrapObservable(cssClass);
                            ko.utils.toggleDomNodeCssClass(element, value, shouldHaveClass);
                        });
                    } else if (_.isObject(singleValueOrArrayOrObject)) {
                        _.each(singleValueOrArrayOrObject, function(classname, condition) {
                            if(ko.utils.unwrapObservable(condition)) {
                                ko.utils.toggleDomNodeCssClass(element, classname, shouldHaveClass);
                            }
                        })

                    } else if (singleValueOrArrayOrObject) {
                        ko.utils.toggleDomNodeCssClass(element, singleValueOrArrayOrObject, shouldHaveClass);
                    }
                };

            // Remove old value(s) (preserves any existing CSS classes)
            addOrRemoveClasses(prevValue, false);

            // Set new value(s)
            addOrRemoveClasses(currentValue, true);

            // Store a copy of the current value
            element['__ko__previousClassValue__'] = currentValue.concat();
        }
    };
});