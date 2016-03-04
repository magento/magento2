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
                targetWizard: '${ $.wizardName }'
            }
        },

        /**
         * Open modal
         */
        openModal: function () {
            this.form().validate();

            if (this.form().source.get('params.invalid') === false && this.targetWizard()) {
                this._super();
                this.targetWizard().open();
            }
        }
    });
});
