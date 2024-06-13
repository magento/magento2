/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
