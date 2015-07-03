/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (ko, Component, stepNavigator) {
        var steps = stepNavigator.steps;
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/progress-bar',
                visible: true
            },
            steps: steps,

            sortItems: function(itemOne, itemTwo) {
                return stepNavigator.sortItems(itemOne, itemTwo);
            },

            navigateTo: function(step) {
                stepNavigator.navigateTo(step);
            },

            isProcessed: function(item) {
                return stepNavigator.isProcessed(item.code);
            }
        });
    }
);
