/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'underscore'
], function (Modal, registry, _) {
    'use strict';

    return Modal.extend({
        defaults: {
            stepWizard: '',
            modules: {
                form: '${ $.formName }'
            }
        },

        /**
         * Open modal
         */
        openModal: function () {
            var stepWizard = {};

            this.form().validate();

            if (this.form().source.get('params.invalid') === false) {
                stepWizard = registry.get('index = ' + this.stepWizard);

                if (!_.isUndefined(stepWizard)) {
                    stepWizard.open();
                }

                this._super();
            }
        }
    });
});
