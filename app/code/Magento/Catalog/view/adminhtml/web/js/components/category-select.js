/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/ui-select',
    'mage/translate',
    'underscore'
], function (Select, $t, _) {
    'use strict';

    return Select.extend({
        defaults: {
            missingValuePlaceholder: $t('%s with ID: %s doesn\'t exist')
        },

        /**
         * Add error placeholder if selected option has been removed
         * {@inheritDoc}
         */
        setCaption: function () {
            var length,
                isARemovedOption;

            if (!_.isArray(this.value()) && this.value()) {
                length = 1;
            } else if (this.value()) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }

            isARemovedOption = length && !this.getSelected().length;

            if (isARemovedOption) {
                this.placeholder(
                    this.missingValuePlaceholder.replace('%s', $t('Category')).replace('%s', this.value())
                );

                return this.placeholder();
            }

            return this._super();
        }
    });
});
