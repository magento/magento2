/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Swatches/js/swatch-renderer'
], function ($) {
    'use strict';

    $.widget('mage.layeredSwatchRenderer', {
        options: {
            showTooltip: 1,
            swatchElementSelector: '[data-option-type="1"], [data-option-type="2"], ' +
                '[data-option-type="0"], [data-option-type="3"]'
        },

        /**
         * @private
         */
        _create: function () {
            if (this.options.showTooltip === 1) {
                $(this.element).find(this.options.swatchElementSelector).SwatchRendererTooltip();
            }
        }
    });

    return $.mage.layeredSwatchRenderer;
});
