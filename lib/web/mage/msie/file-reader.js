/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        /**
         * Is function already initiated
         * @type {Boolean}
         * @private
         */
        _initiated: false,

        /**
         * Init "readAsBinaryString" function for FileReader class.
         * It need for IE11
         */
        init: function () {
            if (!this._initiated && !FileReader.prototype.readAsBinaryString) {
                FileReader.prototype.readAsBinaryString = this._readAsBinaryString;
            }
            this._initiated = true;
        },

        /**
         * Declare "readAsBinaryString" function for FileReader class
         * @param {Blob} fileData
         * @private
         */
        _readAsBinaryString: function (fileData) {
            var binary = '',
                pt = this,
                reader = new FileReader();

            /**
             * Read file as binary string
             */
            reader.onload = function () {
                var bytes, length, i;

                bytes = new Uint8Array(reader.result);
                length = bytes.length;

                for (i = 0; i < length; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                //pt.result  - readonly so assign binary
                pt.content = binary;
                $(pt).trigger('onload');
            };
            reader.readAsArrayBuffer(fileData);
        }
    };
});
