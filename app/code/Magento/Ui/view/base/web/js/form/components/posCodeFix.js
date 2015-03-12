/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    var postCodeName = 'customer_form.groups.customer_addressCollection.new_0.postcode.0',
        countryName = 'customer_form.groups.customer_addressCollection.new_0.country_id.0';
    return {
        //'initProperties': function () {
        //    this._super();
        //    return this;
        //},
        'initListeners': function () {
            this._super();

            if (this.name === countryName) {
                var options = this.options;
                this.value.subscribe(function (newValue) {
                    var option = _.filter(options(), function (op) {
                        return op.value === newValue;
                    });
                    console.log(newValue);
                    console.log(option);
                });
            }

            return this;
        }
    };
});