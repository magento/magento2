/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/website',
    'uiRegistry',
    'underscore'
], function (Website, registry, _) {
    'use strict';

    return Website.extend({
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var groupIdFieldKey = 'group_id',
                sendEmailStoreIdFieldKey = 'sendemail_store_id',
                groupId = registry.get('index = ' + groupIdFieldKey),
                sendEmailStoreId = registry.get('index = ' + sendEmailStoreIdFieldKey),
                customerAttributes = registry.filter('parentScope = data.customer'),
                option = this.getOption(value);

            customerAttributes.forEach(element => {
                var requiredWebsites = element.validation['required-entry-website'];

                if (!_.isArray(requiredWebsites)) {
                    return;
                }
                if (requiredWebsites.includes(parseInt(value, 10))) {
                    element.validation['required-entry'] = true;
                    element.required(true);
                } else {
                    delete element.validation['required-entry'];
                    element.required(false);
                }
            });

            if (groupId) {
                groupId.value(option[groupIdFieldKey]);
            }

            if (sendEmailStoreId && option['default_store_view_id']) {
                sendEmailStoreId.value(option['default_store_view_id']);
            }
            return this._super();
        }
    });
});
