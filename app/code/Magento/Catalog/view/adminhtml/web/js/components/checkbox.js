/* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'Magento_Ui/js/form/element/abstract',
    'knockout'
], function (Abstract, ko) {
    'use strict';

    return Abstract.extend({

        /**
         * Initializes observable properties of instance
         *
         * @returns {Element} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('checked');

            this.value = ko.pureComputed({

                /**
                 * use 'mappedValue' as value if checked
                 */
                read: function () {
                    return this.checked() ? this.mappedValue : '';
                },

                /**
                 * any value made checkbox checked
                 */
                write: function (val) {
                    if (val) {
                        this.checked(true);
                    }
                },
                owner: this
            });

            return this;
        }
    });
});
