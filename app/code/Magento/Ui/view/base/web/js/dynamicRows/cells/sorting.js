/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    '../../form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Extends instance with defaults, extends config with formatted values
         *     and options, and invokes initialize method of AbstractElement class.
         *     If instance's 'customEntry' property is set to true, calls 'initInput'
         */
        initialize: function () {
            this._super()
                .initValue();

            return this;
        },

        /**
         * Set value when component initialized
         */
        initValue: function () {
            this.value(this.source.get(this.dataScope));
        }
    });
});
