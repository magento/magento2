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
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels'
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
        doSelectedAttributesLabels: function(selected) {
            this.selected = selected;
            var labels = [];
            _.each(this.multiselect().rows(), function(attribute) {
                if (_.contains(selected, attribute.attribute_id)) {
                    labels.push(attribute.frontend_label);
                }
            });
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
