/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'mageUtils'
], function (Abstract, utils) {
    'use strict';

    return Abstract.extend({
        defaults: {
            sourceCode: null,
            qtyAvailable: 0
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            var path,
                qtyToShip,
                isManageStock;

            this._super();

            //TODO: Is it right way?
            path = utils.getPart(utils.getPart(this.parentScope, -2), -2);
            qtyToShip = this.source.get(path + '.qtyToShip');
            isManageStock = this.source.get(path + '.isManageStock');

            this.validation['less-than-equals-to'] = isManageStock ? this.qtyAvailable : qtyToShip;

            return this;
        },

        /**
         * Toggle disabled state.
         *
         * @param {String} selected
         */
        toggleDisable: function (selected) {
            this.disabled(selected ? selected.toString() !== this.sourceCode : !selected);
        }
    });
});
