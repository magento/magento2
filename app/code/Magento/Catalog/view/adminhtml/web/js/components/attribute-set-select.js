/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
