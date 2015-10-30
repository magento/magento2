/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    './abstract'
], function (utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {},

        /**
         * Defines form ID with which file input will be associated.
         *
         * @returns {Media} Chainable.
         */
        initProperties: function () {
            var scope = this.dataScope,
                inputName = scope.split('.').slice(1);

            this._super();

            this.inputName = utils.serializeName(inputName.join('.'));

            if (!this.formId) {
                this.formId = this.name.split('.')[0];
            }

            return this;
        }
    });
});
