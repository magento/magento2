/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/select',
    'underscore'
], function (Select, _) {
    'use strict';

    return Select.extend({

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            _.each(this.source.get('data.sourceCodes'), function (label, value) {
                this.options.push({
                    value: value,
                    label: label
                });
            }.bind(this));

            return this;
        }
    });
});
