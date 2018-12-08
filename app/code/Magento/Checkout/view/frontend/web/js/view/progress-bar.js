/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator'
], function ($, _, ko, Component, stepNavigator) {
    'use strict';

    var steps = stepNavigator.steps;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/progress-bar',
            visible: true
        },
        steps: steps,

        /** @inheritdoc */
        initialize: function () {
            var stepsValue;

            this._super();
<<<<<<< HEAD
            $(window).hashchange(_.bind(stepNavigator.handleHash, stepNavigator));
=======
            window.addEventListener('hashchange', _.bind(stepNavigator.handleHash, stepNavigator));
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

            if (!window.location.hash) {
                stepsValue = stepNavigator.steps();

                if (stepsValue.length) {
                    stepNavigator.setHash(stepsValue.sort(stepNavigator.sortItems)[0].code);
                }
            }

            stepNavigator.handleHash();
        },

        /**
         * @param {*} itemOne
         * @param {*} itemTwo
         * @return {*|Number}
         */
        sortItems: function (itemOne, itemTwo) {
            return stepNavigator.sortItems(itemOne, itemTwo);
        },

        /**
         * @param {Object} step
         */
        navigateTo: function (step) {
            stepNavigator.navigateTo(step.code);
        },

        /**
         * @param {Object} item
         * @return {*|Boolean}
         */
        isProcessed: function (item) {
            return stepNavigator.isProcessed(item.code);
        }
    });
});
