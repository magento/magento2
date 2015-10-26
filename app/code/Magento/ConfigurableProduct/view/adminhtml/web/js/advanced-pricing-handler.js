/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Catalog/catalog/type-events'
], function ($, productType) {
    'use strict';

    return {
        $links: $('[data-ui-id=product-tabs-tab-link-advanced-pricing]'),
        $tab: $('[data-tab-panel=advanced-pricing]'),
        toggleDisabledAttribute: function (disabled) {
            $('input,select', this.$tab).each(function (index, element) {
                $(element).attr('disabled', disabled);
            });
        },
        init: function () {
            $(document).on('changeTypeProduct', this._initType.bind(this));
            this._initType();
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
