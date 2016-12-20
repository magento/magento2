/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate'
], function (Component, $, _) {
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
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedAttributes: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedAttributes']);

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
        }
    });
});
