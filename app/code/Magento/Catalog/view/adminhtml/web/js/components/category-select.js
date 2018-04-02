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
            missingValuePlaceholder: $t('%s with ID: %s doesn\'t exist'),
            filterPlaceholder: $t('Category Name'),
            showXWhenOptionSelected: true
        },

        /**
         * Add error placeholder if selected option has been removed
         * {@inheritDoc}
         */
        setCaption: function () {
            var length,
                isARemovedOption,
                caption;

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
                caption = this.missingValuePlaceholder.replace('%s', $t('Category')).replace('%s', this.value());

                this.placeholder(caption);

                // set warning state
                this.warn(caption);

                return this.placeholder();
            }

            // reset warning state
            this.warn('');

            return this._super();
        }
    });
});
