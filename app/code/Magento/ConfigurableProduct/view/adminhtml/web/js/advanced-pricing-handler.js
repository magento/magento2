/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        init: function () {
            $('[data-form=edit-product]')
                .on('change_configurable_type', function (event, isConfigurable) {
                    var toggleDisabledAttribute = function (disabled) {
                        $('[data-tab-panel=advanced-pricing]').find('input,select').each(
                            function (event, element) {
                                $(element).attr('disabled', disabled);
                            }
                        );
                    };
                    if (isConfigurable) {
                        $('[data-ui-id=product-tabs-tab-link-advanced-pricing]').hide();
                    } else {
                        $('[data-ui-id=product-tabs-tab-link-advanced-pricing]').show();
                    }
                    toggleDisabledAttribute(isConfigurable);
                });
        }
    };
});
