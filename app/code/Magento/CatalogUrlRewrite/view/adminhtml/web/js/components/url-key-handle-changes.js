/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            checkedState: false
        },

        /**
         * Extends with checkedState property.
         *
         * @returns {Element}
         */
        initObservable: function () {
            this._super();

            this.checkedState = this.checked.peek();
            this.checked(!this.disabled());
            this.on('checked', function (checkedState) {
                this.checkedState = checkedState;
            }.bind(this));

            return this;
        },

        /**
         * Disable checkbox field, when 'url_key' field without changes
         *
         * @param {String} newValue - user-input value
         */
        handleChanges: function (newValue) {
            var localCheckedState;

            if (this.getReverseValueMap(newValue)) { // changed UrlKeyValue is equal stored UrlKeyValue
                localCheckedState = this.checked.peek();

                this.disabled(true);

                this.checked(false);
                this.checkedState = localCheckedState;
            } else {                                // UrlKeyValue was changed and is needed a rewrite
                localCheckedState = this.checkedState;

                this.disabled(false);

                if (localCheckedState !== this.checked.peek()) {
                    this.checked(localCheckedState);
                    this.checkedState = localCheckedState;
                }
            }
        }
    });
});
