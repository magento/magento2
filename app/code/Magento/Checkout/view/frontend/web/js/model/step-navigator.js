/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko'
    ],
    function($, ko) {
        var steps = ko.observableArray();

        return {
            steps: steps,
            stepCodes: [],
            registerStep: function(code, title, isVisible, sortOrder) {
                steps.push({
                    code: code,
                    title : title,
                    isVisible: isVisible,
                    sortOrder: sortOrder
                });
                this.stepCodes.push(code);
            },

            sortItems: function(itemOne, itemTwo) {
                return itemOne.sortOrder > itemTwo.sortOrder ? 1 : -1
            },

            getActiveItemIndex: function() {
                var activeIndex = 0;
                steps.sort(this.sortItems).forEach(function(element, index) {
                    if (element.isVisible()) {
                        activeIndex = index;
                    }
                });
                return activeIndex;
            },

            isAvailable: function(code) {
                var flag = false;
                this.stepCodes.forEach(function(element){
                    if (element.code == code) {
                        flag = true;
                    }
                });
                return flag;
            },

            isProcessed: function(code) {
                var activeItemIndex = this.getActiveItemIndex();
                var sortedItems = steps.sort(this.sortItems);
                var requestedItemIndex = 0;
                sortedItems.forEach(function(element, index) {
                    if (element.code == code) {
                        requestedItemIndex = index;
                    }
                });
                return activeItemIndex > requestedItemIndex;
            },

            navigateTo: function(step) {
                var activeItemIndex = this.getActiveItemIndex();
                var sortedItems = steps.sort(this.sortItems);
                var stepIndex = sortedItems.indexOf(step);
                if (!this.isProcessed(step.code)) {
                    return;
                }
                sortedItems.forEach(function(element) {
                    if (element.code == step.code) {
                        element.isVisible(true);
                    } else {
                        element.isVisible(false);
                    }

                });
            },

            next: function() {
                var activeIndex = 0;
                steps.sort(this.sortItems).forEach(function(element, index) {
                    if (element.isVisible()) {
                        element.isVisible(false);
                        activeIndex = index;
                    }
                });
                if (steps().length > activeIndex + 1) {
                    steps()[activeIndex + 1].isVisible(true);
                }
            },

            back: function() {
                var activeIndex = 0;
                steps.sort(this.sortItems).forEach(function(element, index) {
                    if (element.isVisible()) {
                        element.isVisible(false);
                        activeIndex = index;
                    }
                });
                if (steps()[activeIndex - 1]) {
                    steps()[activeIndex - 1].isVisible(true);
                }
            }
        };
    }
);
