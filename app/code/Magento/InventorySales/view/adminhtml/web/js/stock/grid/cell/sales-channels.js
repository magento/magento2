/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'mage/template',
    'text!Magento_InventorySales/template/stock/grid/cell/sales-channel-content.html'
], function (Column, mageTemplate, channelTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_InventorySales/stock/grid/cell/sales-channel-cell.html'
        },

        /**
         * render all sales channel records and return complete html
         * each over all sales channels and render html for each channel
         *
         * @param {Object} records contains all results
         * @return {String} return rendered html content
         */
        renderRecords: function (records) {
            var salesChannels = records['sales_channels'],
                htmlContent = '',
                channelType;

            if (typeof salesChannels !== 'object') {
                throw new Error("Provided wrong salesChannel type " + typeof salesChannels + ' the correct type would be object.');
            }
            for (channelType in salesChannels) {
                htmlContent += mageTemplate(
                    channelTemplate,
                    {
                        data: {
                            channelType: channelType.charAt(0).toUpperCase() + channelType.slice(1),
                            values: salesChannels[channelType]
                        }
                    }
                );
            }

            return htmlContent;
        }
    });
});
