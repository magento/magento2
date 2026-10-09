/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment/renderer-list',
    'uiLayout',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'mage/translate',
    'uiRegistry'
], function (_, ko, utils, Component, paymentMethods, rendererList, layout, checkoutDataResolver, $t, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment-methods/list',
            visible: paymentMethods().length > 0,
            configDefaultGroup: {
                name: 'methodGroup',
                component: 'Magento_Checkout/js/model/payment/method-group'
            },
            paymentGroupsList: [],
            defaultGroupTitle: $t('Select a new payment method')
        },

        /**
         * Initialize view.
         *
         * @returns {Component} Chainable.
         */
        initialize: function () {
            this._super().initDefaulGroup().initChildren();
            paymentMethods.subscribe(
                function (changes) {
                    checkoutDataResolver.resolvePaymentMethod();
                    //remove renderer for "deleted" payment methods
                    _.each(changes, function (change) {
                        if (change.status === 'deleted') {
                            this.removeRenderer(change.value.method);
                        }
                    }, this);
                    //add renderer for "added" payment methods
                    _.each(changes, function (change) {
                        if (change.status === 'added') {
                            this.createRenderer(change.value);
                        }
                    }, this);
                }, this, 'arrayChange');

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().
                observe(['paymentGroupsList']);

            return this;
        },

        /**
         * Creates default group
         *
         * @returns {Component} Chainable.
         */
        initDefaulGroup: function () {
            layout([
                this.configDefaultGroup
            ]);

            return this;
        },

        /**
         * Create renders for child payment methods.
         *
         * @returns {Component} Chainable.
         */
        initChildren: function () {
            var self = this;

            _.each(paymentMethods(), function (paymentMethodData) {
                self.createRenderer(paymentMethodData);
            });

            return this;
        },

        /**
         * @returns
         */
        createComponent: function (payment) {
            var rendererTemplate,
                rendererComponent,
                templateData;

            templateData = {
                parentName: this.name,
                name: payment.name
            };
            rendererTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                displayArea: payment.displayArea,
                component: payment.component,
                sortOrder: payment.sortOrder
            };
            rendererComponent = utils.template(rendererTemplate, templateData);
            utils.extend(rendererComponent, {
                item: payment.item,
                config: payment.config
            });

            return rendererComponent;
        },

        /**
         * Checks whether the given renderer applies to the given payment method code.
         *
         * @param {Object} renderer
         * @param {String} method
         * @returns {Boolean}
         */
        isRendererForMethod: function (renderer, method) {
            if (renderer.hasOwnProperty('typeComparatorCallback') &&
                typeof renderer.typeComparatorCallback == 'function'
            ) {
                return renderer.typeComparatorCallback(renderer.type, method);
            }

            return renderer.type === method;
        },

        /**
         * Counts how many renderers apply to the given payment method code.
         *
         * @param {String} method
         * @returns {Number}
         */
        countRenderersForMethod: function (method) {
            return _.filter(rendererList(), function (renderer) {
                return this.isRendererForMethod(renderer, method);
            }, this).length;
        },

        /**
         * Create renderer.
         *
         * @param {Object} paymentMethodData
         */
        createRenderer: function (paymentMethodData) {
            var currentGroup,
                sortOrder = 0;

            _.some(paymentMethods(), function (method) {
                if (method.method === paymentMethodData.method) {
                    return true;
                }
                sortOrder += this.countRenderersForMethod(method.method);

                return false;
            }, this);

            registry.get(this.configDefaultGroup.name, function (defaultGroup) {
                _.each(rendererList(), function (renderer) {
                    if (!this.isRendererForMethod(renderer, paymentMethodData.method)) {
                        return;
                    }

                    currentGroup = renderer.group ? renderer.group : defaultGroup;

                    this.collectPaymentGroups(currentGroup);

                    layout([
                        this.createComponent(
                            {
                                config: renderer.config,
                                component: renderer.component,
                                name: renderer.type,
                                method: paymentMethodData.method,
                                item: paymentMethodData,
                                displayArea: currentGroup.displayArea,
                                sortOrder: sortOrder
                            }
                        )]);

                    sortOrder++;
                }.bind(this));
            }.bind(this));
        },

        /**
         * Collects unique groups of available payment methods
         *
         * @param {Object} group
         */
        collectPaymentGroups: function (group) {
            var groupsList = this.paymentGroupsList(),
                isGroupExists = _.some(groupsList, function (existsGroup) {
                    return existsGroup.alias === group.alias;
                });

            if (!isGroupExists) {
                groupsList.push(group);
                groupsList = _.sortBy(groupsList, function (existsGroup) {
                    return existsGroup.sortOrder;
                });
                this.paymentGroupsList(groupsList);
            }
        },

        /**
         * Returns payment group title
         *
         * @param {Object} group
         * @returns {String}
         */
        getGroupTitle: function (group) {
            var title = group().title;

            if (group().isDefault() && this.paymentGroupsList().length > 1) {
                title = this.defaultGroupTitle;
            }

            return title;
        },

        /**
         * Checks if at least one payment method available
         *
         * @returns {String}
         */
        isPaymentMethodsAvailable: function () {
            return _.some(this.paymentGroupsList(), function (group) {
                return this.regionHasElements(group.displayArea);
            }, this);
        },

        /**
         * Remove view renderer.
         *
         * @param {String} paymentMethodCode
         */
        removeRenderer: function (paymentMethodCode) {
            var items;

            _.each(this.paymentGroupsList(), function (group) {
                items = this.getRegion(group.displayArea);

                _.find(items(), function (value) {
                    if (value.item.method.indexOf(paymentMethodCode) === 0) {
                        value.disposeSubscriptions();
                        value.destroy();
                    }
                });
            }, this);
        }
    });
});
