/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'mage/template',
    'text!Magento_InventorySales/template/stock/grid/cell/sales-channel-content.html',
    'mage/translate'
], function (Column, mageTemplate, channelTemplate, $t) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_InventorySales/stock/grid/cell/sales-channel-cell.html'
        },

        /**
         * render all sales channel records and return complete html
         *
         * @param records {object} contains all results
         * @returns {string} return rendered html content
         */
        renderRecords: function (records) {
            var sales_channels = records['sales_channels'];
            var htmlContent = '';

            /**
             * each over all sales channels and render html for each channel
             */
            for (var channelType in sales_channels) {
                var channelTypeString = channelType.charAt(0).toUpperCase() + channelType.slice(1);
                htmlContent = htmlContent + mageTemplate(
                    channelTemplate,
                    {
                        data: {
                            channelType: $t(channelTypeString),
                            values: sales_channels[channelType]
                        }
                    });
            }

            return htmlContent;
        }
    });
});
