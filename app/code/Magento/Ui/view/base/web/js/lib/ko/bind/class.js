/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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