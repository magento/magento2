/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
define([
    'Magento_Ui/js/form/components/fieldset',
    'Magento_Catalog/js/components/visible-on-option/strategy'
], function (Fieldset, strategy) {
    'use strict';

    return Fieldset.extend(strategy).extend(
        {
            defaults: {
                openOnShow: true
            },

            /**
             * Toggle visibility state.
             */
            toggleVisibility: function () {
                this._super();

                if (this.openOnShow) {
                    this.opened(this.inverseVisibility ? !this.isShown : this.isShown);
                }
            }
        }
    );
});
