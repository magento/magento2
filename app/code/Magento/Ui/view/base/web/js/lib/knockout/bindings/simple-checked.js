/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    '../template/renderer'
], function (ko, renderer) {
    'use strict';

    ko.bindingHandlers.simpleChecked = {
        'after': ['attr'],

        /**
         * Implements same functionality as a standard 'simpleChecked' binding,
         * but with a difference that it wont' change values array if
         * value of DOM element changes.
         */
        init: function (element, valueAccessor) {
            var isCheckbox = element.type === 'checkbox',
                isRadio = element.type === 'radio',
                updateView,
                updateModel;

            if (!isCheckbox && !isRadio) {
                return;
            }

            /**
             * Updates checked observable
             */
            updateModel = function () {
                var  modelValue = ko.dependencyDetection.ignore(valueAccessor),
                    isChecked = element.checked;

                if (ko.computedContext.isInitial()) {
                    return;
                }

                if (modelValue.peek() === isChecked) {
                    return;
                }

                if (isRadio && !isChecked) {
                    return;
                }

                modelValue(isChecked);
            };

            /**
             * Updates checkbox state
             */
            updateView = function () {
                var modelValue = ko.utils.unwrapObservable(valueAccessor());

                element.checked = !!modelValue;
            };

            ko.utils.registerEventHandler(element, 'change', updateModel);

            ko.computed(updateModel, null, {
                disposeWhenNodeIsRemoved: element
            });
            ko.computed(updateView, null, {
                disposeWhenNodeIsRemoved: element
            });
        }
    };

    ko.expressionRewriting.twoWayBindings.simpleChecked = true;

    renderer.addAttribute('simpleChecked');
    renderer.addAttribute('simple-checked', {
        binding: 'simpleChecked'
    });
});
