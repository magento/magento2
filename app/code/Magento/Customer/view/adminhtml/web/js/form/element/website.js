/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/website',
    'uiRegistry'
], function (Website, registry) {
    'use strict';

    return Website.extend({
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var groupIdFieldKey = 'group_id',
                groupId = registry.get('index = ' + groupIdFieldKey),
                option = this.getOption(value);

            if (groupId) {
                groupId.value(option[groupIdFieldKey]);
            }

            if (option['default_store_view_id']) {
                document.getElementsByName("customer[sendemail_store_id]")[0].value = option['default_store_view_id'];
            }
            return this._super();
        }
    });
});
