/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            valueUpdate: 'input',
            selectionQtyIsInteger: false,
            selectionQtyIsIntegerKey: 'selection_qty_is_integer'
        },

        getFromString: function(o, s) {
            s = s.replace(/\[(\w+)\]/g, '.$1'); // convert indexes to properties
            s = s.replace(/^\./, '');           // strip a leading dot
            var a = s.split('.');
            for (var i = 0, n = a.length; i < n; ++i) {
                var k = a[i];
                if (k in o) {
                    o = o[k];
                } else {
                    return;
                }
            }
            return o;
        },

        /**
         * update event
         */
        onUpdate: function () {
            var isInteger = this.getFromString(this.source,this.parentScope + '.' + this.selectionQtyIsIntegerKey);
            if (isInteger) {
                this.selectionQtyIsInteger = false;
            }

            this.validation['validate-number'] = true;

            this.validation['validate-digits'] = isInteger;
            this.validate();
        }
    });
});
