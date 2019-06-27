/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'mageUtils'
    ], function (ko, utils) {
    'use strict';

    var captionPlaceholder = {},
        optgroupTmpl = '<optgroup label="${ $.label }"></optgroup>',
        nbspRe = /&nbsp;/g,
        optionsText,
        optionsValue,
        optionTitle;

    ko.bindingHandlers.optgroup = {
        /**
         * @param {*} element
         * @returns {Object}
         */
        init: function (element) {
            if (ko.utils.tagNameLower(element) !== 'select') {
                throw new Error('options binding applies only to SELECT elements');
            }

            // Remove all existing <option>s.
            while (element.length > 0) {
                element.remove(0);
            }

            // Ensures that the binding processor doesn't try to bind the options
            return {
                'controlsDescendantBindings': true
            };
        },

        /**
         * @param {*} element
         * @param {*} valueAccessor
         * @param {*} allBindings
         */
        update: function (element, valueAccessor, allBindings) {
            var selectWasPreviouslyEmpty = element.length === 0,
                previousScrollTop = !selectWasPreviouslyEmpty && element.multiple ? element.scrollTop : null,
                includeDestroyed = allBindings.get('optionsIncludeDestroyed'),
                arrayToDomNodeChildrenOptions = {},
                captionValue,
                unwrappedArray = ko.utils.unwrapObservable(valueAccessor()),
                filteredArray,
                previousSelectedValues,
                itemUpdate = false,
                callback = setSelectionCallback,//eslint-disable-line no-use-before-define
                nestedOptionsLevel = -1;

            optionsText = ko.utils.unwrapObservable(allBindings.get('optionsText')) || 'text';
            optionsValue = ko.utils.unwrapObservable(allBindings.get('optionsValue')) || 'value';
            optionTitle = optionsText + 'title';

            if (element.multiple) {
                previousSelectedValues = ko.utils.arrayMap(
                    selectedOptions(),//eslint-disable-line no-use-before-define
                    ko.selectExtensions.readValue
                );
            } else {
                previousSelectedValues = element.selectedIndex >= 0 ?
                    [ko.selectExtensions.readValue(element.options[element.selectedIndex])] :
                    [];
            }

            if (unwrappedArray) {
                if (typeof unwrappedArray.length === 'undefined') { // Coerce single value into array
                    unwrappedArray = [unwrappedArray];
                }

                // Filter out any entries marked as destroyed
                filteredArray = ko.utils.arrayFilter(unwrappedArray, function (item) {
                    if (item && !item.label) {
                        return false;
                    }

                    return includeDestroyed ||
                        item === undefined ||
                        item === null ||
                        !ko.utils.unwrapObservable(item._destroy);
                });
                filteredArray.map(recursivePathBuilder, null);//eslint-disable-line no-use-before-define
            }

            /**
             * @param {*} option
             */
            arrayToDomNodeChildrenOptions.beforeRemove = function (option) {
                element.removeChild(option);
            };

            if (allBindings.has('optionsAfterRender')) {

                /**
                 * @param {*} arrayEntry
                 * @param {*} newOptions
                 */
                callback = function (arrayEntry, newOptions) {
                    setSelectionCallback(arrayEntry, newOptions);//eslint-disable-line no-use-before-define
                    ko.dependencyDetection.ignore(
                        allBindings.get('optionsAfterRender'),
                        null,
                        [newOptions[0],
                        arrayEntry !== captionPlaceholder ? arrayEntry : undefined]
                    );
                };
            }

            filteredArray = formatOptions(filteredArray);//eslint-disable-line no-use-before-define
            ko.utils.setDomNodeChildrenFromArrayMapping(
                element,
                filteredArray,
                optionNodeFromArray,//eslint-disable-line no-use-before-define
                arrayToDomNodeChildrenOptions,
                callback
            );

            ko.dependencyDetection.ignore(function () {
                var selectionChanged;

                if (allBindings.get('valueAllowUnset') && allBindings.has('value')) {
                    // The model value is authoritative, so make sure its value is the one selected
                    ko.selectExtensions.writeValue(
                        element,
                        ko.utils.unwrapObservable(allBindings.get('value')),
                        true /* allowUnset */
                    );
                } else {
                    // Determine if the selection has changed as a result of updating the options list
                    if (element.multiple) {
                        // For a multiple-select box, compare the new selection count to the previous one
                        // But if nothing was selected before, the selection can't have changed
                        selectionChanged = previousSelectedValues.length &&
                            selectedOptions().length < //eslint-disable-line no-use-before-define
                            previousSelectedValues.length;
                    } else {
                        // For a single-select box, compare the current value to the previous value
                        // But if nothing was selected before or nothing is selected now,
                        // just look for a change in selection
                        selectionChanged = previousSelectedValues.length && element.selectedIndex >= 0 ?
                            ko.selectExtensions.readValue(element.options[element.selectedIndex]) !==
                            previousSelectedValues[0] : previousSelectedValues.length || element.selectedIndex >= 0;
                    }

                    // Ensure consistency between model value and selected option.
                    // If the dropdown was changed so that selection is no longer the same,
                    // notify the value or selectedOptions binding.
                    if (selectionChanged) {
                        ko.utils.triggerEvent(element, 'change');
                    }
                }
            });

            /*eslint-enable max-len, no-use-before-define*/

            if (previousScrollTop && Math.abs(previousScrollTop - element.scrollTop) > 20) {
                element.scrollTop = previousScrollTop;
            }

            /**
             * @returns {*}
             */
            function selectedOptions() {
                return ko.utils.arrayFilter(element.options, function (node) {
                    return node.selected;
                });
            }

            /**
             * @param {*} object
             * @param {*} predicate
             * @param {*} defaultValue
             * @returns {*}
             */
            function applyToObject(object, predicate, defaultValue) {
                var predicateType = typeof predicate;

                if (predicateType === 'function') {   // run it against the data value
                    return predicate(object);
                } else if (predicateType === 'string') { // treat it as a property name on the data value
                    return object[predicate];
                }

                return defaultValue;
            }

            /**
             * @param {*} obj
             */
            function recursivePathBuilder(obj) {

                obj[optionTitle] = (this && this[optionTitle] ? this[optionTitle] + '/' : '') + obj[optionsText].trim();

                if (Array.isArray(obj[optionsValue])) {
                    obj[optionsValue].map(recursivePathBuilder, obj);
                }
            }

            /**
             * @param {Array} arrayEntry
             * @param {*} oldOptions
             * @returns {*[]}
             */
            function optionNodeFromArray(arrayEntry, oldOptions) {
                var option;

                if (oldOptions.length) {
                    previousSelectedValues = oldOptions[0].selected ?
                        [ko.selectExtensions.readValue(oldOptions[0])] : [];
                    itemUpdate = true;
                }

                if (arrayEntry === captionPlaceholder) { // empty value, label === caption
                    option = element.ownerDocument.createElement('option');
                    ko.utils.setTextContent(option, allBindings.get('optionsCaption'));
                    ko.selectExtensions.writeValue(option, undefined);
                } else if (typeof arrayEntry[optionsValue] === 'undefined') { // empty value === optgroup
                    if (arrayEntry.__disableTmpl) {
                        option = '<optgroup label="' + arrayEntry[optionsText] + '"></optgroup>';
                    } else {
                        option = utils.template(optgroupTmpl, {
                            label: arrayEntry[optionsText],
                            title: arrayEntry[optionsText + 'title']
                        });
                    }
                    option = ko.utils.parseHtmlFragment(option)[0];

                } else {
                    option = element.ownerDocument.createElement('option');
                    option.setAttribute('data-title', arrayEntry[optionsText + 'title']);
                    ko.selectExtensions.writeValue(option, arrayEntry[optionsValue]);
                    ko.utils.setTextContent(option, arrayEntry[optionsText]);
                }

                return [option];
            }

            /**
             * @param {*} newOptions
             */
            function setSelectionCallback(newOptions) {
                var isSelected;

                // IE6 doesn't like us to assign selection to OPTION nodes before they're added to the document.
                // That's why we first added them without selection. Now it's time to set the selection.
                if (previousSelectedValues.length) {
                    isSelected = ko.utils.arrayIndexOf(
                        previousSelectedValues,
                        ko.selectExtensions.readValue(newOptions.value)
                    ) >= 0;

                    ko.utils.setOptionNodeSelectionState(newOptions.value, isSelected);

                    // If this option was changed from being selected during a single-item update, notify the change
                    if (itemUpdate && !isSelected) {
                        ko.dependencyDetection.ignore(ko.utils.triggerEvent, null, [element, 'change']);
                    }
                }
            }

            /**
             * @param {*} string
             * @param {Number} times
             * @returns {Array}
             */
            function strPad(string, times) {
                return (new Array(times + 1)).join(string);
            }

            /**
             * @param {*} options
             * @returns {Array}
             */
            function formatOptions(options) {
                var res = [];

                nestedOptionsLevel++;

                if (!nestedOptionsLevel) { // zero level
                    // If caption is included, add it to the array
                    if (allBindings.has('optionsCaption')) {
                        captionValue = ko.utils.unwrapObservable(allBindings.get('optionsCaption'));
                        // If caption value is null or undefined, don't show a caption
                        if (//eslint-disable-line max-depth
                            captionValue !== null &&
                            captionValue !== undefined &&
                            captionValue !== false
                        ) {
                            res.push(captionPlaceholder);
                        }
                    }
                }

                ko.utils.arrayForEach(options, function (option) {
                    var value = applyToObject(option, optionsValue, option),
                        label = applyToObject(option, optionsText, value) || '',
                        disabled = applyToObject(option, 'disabled', false) || false,
                        obj = {},
                        space = '\u2007\u2007\u2007';

                    obj[optionTitle] = applyToObject(option, optionsText + 'title', value);

                    if (disabled) {
                        obj.disabled = disabled;
                    }

                    if (option.hasOwnProperty('__disableTmpl')) {
                        obj.__disableTmpl = option.__disableTmpl;
                    }

                    label = label.replace(nbspRe, '').trim();

                    if (Array.isArray(value)) {
                        obj[optionsText] = strPad('&nbsp;', nestedOptionsLevel * 4) + label;
                        res.push(obj);
                        res = res.concat(formatOptions(value));
                    } else {
                        obj[optionsText] = strPad(space, nestedOptionsLevel * 2) + label;
                        obj[optionsValue] = value;
                        res.push(obj);
                    }
                });
                nestedOptionsLevel--;

                return res;
            }
        }
    };
    ko.bindingHandlers.selectedOptions.after.push('optgroup');
});
