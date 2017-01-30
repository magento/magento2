/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Catalog/catalog/type-events'
], function ($, productType) {
    'use strict';

    return {
        $initiallyDisabledAttributes: [],
        $links: $('[data-ui-id=product-tabs-tab-link-advanced-pricing]'),
        $tab: $('[data-tab-panel=advanced-pricing]'),
        toggleDisabledAttribute: function (disabled) {
            $('input,select', this.$tab).each(function (index, element) {
                if (!$.inArray(element, this.$initiallyDisabledAttributes)) {
                    $(element).attr('disabled', disabled);
                }
            });
        },
        init: function () {
            $(document).on('changeTypeProduct', this._initType.bind(this));
            this._setInitialState();
            this._initType();
        },
        _setInitialState: function () {
            if (this.$initiallyDisabledAttributes.length == 0) {
                this.$initiallyDisabledAttributes = $('input:disabled,select:disabled', this.$tab).toArray();
            }
        },
        _initType: function () {
            var isConfigurable = productType.type.current === 'configurable';

            if (isConfigurable) {
                this.$links.hide();
            } else {
                this.$links.show();
            }

            this.toggleDisabledAttribute(isConfigurable);
        }
    };
});
