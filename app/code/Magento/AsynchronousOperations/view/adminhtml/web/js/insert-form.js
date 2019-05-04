/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-form',
    'uiRegistry'
], function (Insert, registry) {
    'use strict';

    return Insert.extend({
        defaults: {
            modalProvider: '${ $.parentName }',
            titlePrefix: '',
            imports: {
                changeModalTitle: '${ $.modalProvider }:state'
            },
            listens: {
                responseData: 'afterRetry'
            },
            modules: {
                modal: '${ $.modalProvider }',
                notificationListing: '${ $.columnsProvider }'
            }
        },

        /** @inheritdoc */
        initConfig: function () {
            var modalTitleProvider;

            this._super();
            modalTitleProvider = this.modalTitleProvider.split(':');
            this.modalTitleTarget = modalTitleProvider[0];
            this.modalTitlePath = modalTitleProvider[1];
        },

        /**
         * Change modal title.
         *
         * @param {Boolean} change
         */
        changeModalTitle: function (change) {
            if (change) {
                registry.get(this.modalTitleTarget, function (target) {
                    this.modal().setTitle(this.titlePrefix + target.get(this.modalTitlePath));
                }.bind(this));
            } else {
                this.modal().setTitle('');
            }
        },

        /**
         * Action after retry operation.
         *
         * @param {Object} data
         */
        afterRetry: function (data) {
            if (!data.error) {
                this.modal().closeModal();
                this.notificationListing().reload();
            }
        }
    });
});
