/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/ui-select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            listens: {
                'value': 'changeFormSubmitUrl'
            },
            modules: {
                formProvider: '${ $.provider }'
            }
        },

        /**
         * Change set parameter in save and validate urls of form
         *
         * @param {String|Number} value
         */
        changeFormSubmitUrl: function (value) {
            var pattern = /(set\/)(\d)*?\//,
                change = '$1' + value + '/';

            this.formProvider().client.urls.save = this.formProvider().client.urls.save.replace(pattern, change);
            this.formProvider().client.urls.beforeSave = this.formProvider().client.urls.beforeSave.replace(
                pattern,
                change
            );
        }
    });
});
