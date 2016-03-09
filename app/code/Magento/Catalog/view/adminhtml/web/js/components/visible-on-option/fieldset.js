/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
                this.elems.each(function (child) {
                    child.set('visible', this.inverseVisibility ? !this.isShown : this.isShown);
                }.bind(this));
                
                if (this.openOnShow) {
                    this.opened(this.inverseVisibility ? !this.isShown : this.isShown);
                }
            }
        }
    );
});
