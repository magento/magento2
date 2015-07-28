/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "uiComponent",
    "jquery",
    "underscore"
], function (Component, $, _) {
    "use strict";

    var initNewAttributeListener = function (provider) {
        $('[data-role=product-variations-generator]').on('add', function() {
            provider().reload();
        });
    };
    return Component.extend({
        attributesLabels: {},
        initSavedAttributes: true,
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }',
                configurableVariations: '${ "configurableVariations" }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            }
        },
        initialize: function () {
            this._super();
            this.selected = [];

            initNewAttributeListener(this.attributeProvider);
        },
        initObservable: function () {
            this._super().observe(['selectedAttributes']);
            return this;
        },
        render: function (wizard) {
        },
        doSelectSavedAttributes: function() {
            if (this.initSavedAttributes) {
                this.initSavedAttributes = false;
                this.configurableVariations(function (configurableVariations) {
                    this.multiselect().selected(_.pluck(configurableVariations.attributes(), 'id'));
                }.bind(this));
            }
        },
        doSelectedAttributesLabels: function(selected) {
            this.selected = selected;
            var labels = [];
            _.each(selected, function(attributeId) {
                if (!this.attributesLabels[attributeId]) {
                    var attribute = _.findWhere(this.multiselect().rows(), {attribute_id: attributeId});
                    this.attributesLabels[attribute.attribute_id] = attribute.frontend_label;
                }
                labels.push(this.attributesLabels[attributeId]);
            }.bind(this));
            this.selectedAttributes(labels.join(', '));
        },
        force: function (wizard) {
            wizard.data.attributesIds = this.multiselect().selected;

            if (!wizard.data.attributesIds() || wizard.data.attributesIds().length === 0) {
                throw new Error($.mage.__('Please, select attribute(s)'));
            }
        },
        back: function (wizard) {
        }
    });
});
