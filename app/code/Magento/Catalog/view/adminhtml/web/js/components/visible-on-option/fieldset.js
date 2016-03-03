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
            toggleVisibility: function (selected) {
                this._super();
                this.elems.each(function (child) {
                    child.set('visible', this.isShown);
                }.bind(this));
                this.opened(this.isShown);
            }
        }
    );
});
