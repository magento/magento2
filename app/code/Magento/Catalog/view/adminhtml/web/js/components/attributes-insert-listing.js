/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiRegistry',
    'underscore',
    'Magento_Ui/js/form/components/insert-listing'
], function ($, registry, _, InsertListing) {
    'use strict';

    return InsertListing.extend({
        defaults: {
            addAttributeUrl: '',
            attributeSetId: '',
            attributeIds: '',
            groupCode: '',
            groupName: '',
            groupSortOrder: 0,
            productId: 0,
            formProvider: '',
            modules: {
                form: '${ $.formProvider }',
                modal: '${ $.parentName }'
            },
            productType: ''
        },

        /**
         * Render attribute
         */
        render: function () {
            this._super();
        },

        /**
         * Save attribute
         */
        save: function () {
            this.addSelectedAttributes();
            this._super();
        },

        /**
         * Add selected attributes
         */
        addSelectedAttributes: function () {
            $.ajax({
                url: this.addAttributeUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    attributeIds: this.selections().getSelections(),
                    templateId: this.attributeSetId,
                    groupCode: this.groupCode,
                    groupName: this.groupName,
                    groupSortOrder: this.groupSortOrder,
                    productId: this.productId,
                    componentJson: 1
                },
                success: function () {
                    this.form().params = {
                        set: this.attributeSetId,
                        id: this.productId,
                        type: this.productType
                    };
                    this.form().reload();
                    this.modal().state(false);
                    this.reload();
                }.bind(this)
            });
        }
    });
});
