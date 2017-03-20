/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/paging/sizes'
], function (Sizes) {
    'use strict';

    return Sizes.extend({
        defaults: {
            excludedOptions: ['100', '200']
        },

        /**
         * @override
         */
        initialize: function () {
            this._super();

            this.excludedOptions.forEach(function (excludedOption) {
                delete this.options[excludedOption];
            }, this);
            this.updateArray();

            return this;
        }
    });
});
