/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        /** @inheritdoc */
        onUpdate: function () {
            this.bubble('update', this.hasChanged());
        }
    });
});
