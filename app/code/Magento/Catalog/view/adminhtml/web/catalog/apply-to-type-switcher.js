/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    'Magento_Catalog/js/product/weight-handler',
    'Magento_Catalog/catalog/type-events'
], function($, weight, type) {

    return {
        /**
         * Bind event
         */
        bindAll: function () {
            $('[data-form=edit-product] [data-role=tabs]').on('contentUpdated', function() {
                this._switchToTypeByApplyAttr(type.getType().current);
            }.bind(this));

            $("#product_info_tabs").on("beforePanelsMove tabscreate tabsactivate", function() {
                this._switchToTypeByApplyAttr(type.getType().current);
            }.bind(this));

            $(document).on('changeTypeProduct', function (event, controllers) {
                this._switchToTypeByApplyAttr(controllers.type.current);
            }.bind(this));
        },

        /**
         * Show/hide elements based on type
         *
         * @param {string} typeCode
         * @private
         */
        _switchToTypeByApplyAttr: function(typeCode) {
            $('[data-apply-to]:not(.removed)').each(function(index, element) {
                var attrContainer = $(element),
                    applyTo = attrContainer.data('applyTo') || [];
                var $inputs = attrContainer.find('select, input, textarea');
                if (applyTo.length === 0 || $.inArray(typeCode, applyTo) !== -1) {
                    attrContainer.removeClass('not-applicable-attribute');
                    $inputs.removeClass('ignore-validate');
                } else {
                    attrContainer.addClass('not-applicable-attribute');
                    $inputs.addClass('ignore-validate');
                }
            });
        }
    };
});
