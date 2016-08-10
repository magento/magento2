/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'underscore'
], function (Column, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_AdminNotification/grid/cells/message',
            messageIndex: 'text',
            fieldClass: {
                message: true
            }
        },

        /** @inheritdoc */
        getLabel: function (record) {
            return record[this.messageIndex];
        },

        /** @inheritdoc */
        getFieldClass: function ($row) {
            var status = $row.status || 'info';

            this.fieldClass['message-' + status] = true;

            return this.fieldClass;
        }
    });
});
