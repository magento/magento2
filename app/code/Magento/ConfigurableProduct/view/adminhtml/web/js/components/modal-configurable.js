/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/modal/modal-component'
], function (Modal) {
    'use strict';

    return Modal.extend({
        defaults: {
            modules: {
                form: '${ $.formName }',
                wizard: '${ $.wizardName }'
            }
        },

        /**
         * Open modal
         */
        'openModal': function () {
            this.trigger('active', true);
            this.form().validate();
            if (this.form().source.get('params.invalid') === false) {
                this._super();
                this.wizard().open();
            }
        }
    });
});