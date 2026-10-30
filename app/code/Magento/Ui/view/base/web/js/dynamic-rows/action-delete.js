/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: false
            }
        },

        /**
         * Delete record handler.
         *
         * @param {Number} index
         * @param {Number} id
         */
        deleteRecord: function (index, id) {
            this.bubble('deleteRecord', index, id);
        }
    });
});
