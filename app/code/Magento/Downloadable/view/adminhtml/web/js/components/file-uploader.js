/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/file-uploader'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            fileInputName: ''
        },

        /**
         * Adds provided file to the files list.
         *
         * @param {Object} file
         * @returns {FileUploder} Chainable.
         */
        addFile: function (file) {
            var processedFile = this.processFile(file),
                tmpFile = [],
                resultFile = {
                'file': processedFile.file,
                'name': processedFile.name,
                'size': processedFile.size,
                'status': processedFile.status ? processedFile.status : 'new'
            };

            tmpFile[0] = resultFile;

            this.isMultipleFiles ?
                this.value.push(tmpFile) :
                this.value(tmpFile);

            return this;
        }
    });
});
