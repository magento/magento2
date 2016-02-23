/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry'
], function (Modal, registry) {
    'use strict';

    return Modal.extend({

        /**
         * Open modal
         */
        'openModal': function () {
            this.trigger('active', true);
            var form = registry.get('product_form.product_form');
            form.source.trigger('data.validate');
            var validated = form.source.get('params.invalid');
            //if (!validated && typeof(registry.get('variation-steps-wizard')) !== "undefined") {
            if (typeof(registry.get('variation-steps-wizard')) !== "undefined") {
                registry.get('variation-steps-wizard').open();
                this._super();
            }
        }
    });
});