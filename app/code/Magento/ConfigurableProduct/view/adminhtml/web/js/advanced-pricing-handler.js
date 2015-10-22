/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
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
            $(document).on('changeTypeProduct', function (event, controllers) {
                var isConfigurable = controllers.type.current === 'configurable';

                if (isConfigurable) {
                    this.$links.hide();
                } else {
                    this.$links.show();
                }

                this.toggleDisabledAttribute(isConfigurable);
            }.bind(this));
        }
    };
});
