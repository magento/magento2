/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Init "readAsBinaryString" function for FileReader class.
     * It need for IE11
     * @param {Blob} fileData
     */
    var readAsBinaryStringIEFunc = function (fileData) {
        var binary = '',
            self = this,
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
            //self.result  - readonly so assign binary
            self.content = binary;
            $(self).trigger('onload');
        };
        reader.readAsArrayBuffer(fileData);
    };

    if (typeof FileReader.prototype.readAsBinaryString === 'undefined') {
        FileReader.prototype.readAsBinaryString = readAsBinaryStringIEFunc;
    }
});
