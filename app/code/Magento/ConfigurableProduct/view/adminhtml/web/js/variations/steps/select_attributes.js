/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'Magento_ConfigurableProduct/js/components/associated-product-list',
    'Magento_ConfigurableProduct/js/variations/product-grid',
    'mage/translate'
], function (Component, $, _, productList , prdoductGrid ) {
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=product-variations-matrix]').on('add', function () {
            provider().reload();
        });
    };

    return Component.extend({
        attributesLabels: {},
        stepInitialized: false,
        attributesCode: {},
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }',
                variationsComponent: '${ $.variationsComponent }',
                modalComponent: '${ $.modalComponent }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels doSelectedAttributesCodes doShowAddProductButton',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedAttributes: [],
            attributes: [],
            disabledButton: true
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe('selectedAttributes attributes disabledButton');

            return this;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.setNotificationMessage();
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('When you remove or add an attribute, we automatically update all configurations and you will need to recreate current configurations manually.');

            /*eslint-enable max-len*/

            if (this.mode === 'edit') {
                this.wizard.setNotificationMessage(msg);
            }
        },

        /**
         * Do select saved attributes.
         */
        doSelectSavedAttributes: function () {
            if (this.stepInitialized === false) {
                this.stepInitialized = true;
                //cache attributes labels, which can be present on the 2nd page
                _.each(this.initData.attributes, function (attribute) {
                    this.attributesLabels[attribute.id] = attribute.label;
                }.bind(this));
                this.multiselect().selected(_.pluck(this.initData.attributes, 'id'));
            }
        },

        /**
         * @param {*} selected
         */
        doSelectedAttributesLabels: function (selected) {
            var labels = [];

            this.selected = selected;
            _.each(selected, function (attributeId) {
                var attribute;

                if (!this.attributesLabels[attributeId]) {
                    attribute = _.findWhere(this.multiselect().rows(), {
                        'attribute_id': attributeId
                    });

                    if (attribute) {
                        this.attributesLabels[attribute['attribute_id']] = attribute['frontend_label'];
                    }
                }
                labels.push(this.attributesLabels[attributeId]);
            }.bind(this));
            this.selectedAttributes(labels.join(', '));
        },

        /**
         * @param {*} selected
         */
        doSelectedAttributesCodes: function (selected) {
            var code = [];
            this.selected = selected;
            _.each(selected, function (attributeId) {
                var attribute;

                if (!this.attributesCode[attributeId]) {
                    attribute = _.findWhere(this.multiselect().rows(), {
                        'attribute_id': attributeId
                    });
                    if (attribute) {
                        var chosen = {
                            'id': attribute.attribute_id,
                            'attribute_code': attribute.attribute_code,
                            'attribute_label': attribute.frontend_label,
                            'label': '',
                            'value': '0'
                        }
                        var chose = [];
                        chose.push(chosen);
                        var newatrr = {
                            'id': attribute.attribute_id,
                            'code': attribute.attribute_code,
                            'label': attribute.frontend_label,
                            'chosen': chose,
                            'position': 0
                        }
                        this.attributesCode[attribute['attribute_id']] = newatrr;
                    }
                }
                code.push(this.attributesCode[attributeId]);

            }.bind(this));
            this.attributes(code);
        },

        doShowAddProductButton: function (selected) {
            this.disabledButton(!selected.length);
        },


        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.attributesIds = this.multiselect().selected;

            if (!wizard.data.attributesIds() || wizard.data.attributesIds().length === 0) {
                throw new Error($.mage.__('Please select attribute(s).'));
            }
            this.setNotificationMessage();
        },

        /**
         * Back.
         */
        back: function () {
        },
    
        addProductManualy: function (data, event) {
            productList().isShowAddProductButton(true);
            this.variationsComponent().render(null, this.attributes());
            this.modalComponent().closeModal();
            $("[data-index='add_products_manually_button']").trigger('click');
        }
    });
});
