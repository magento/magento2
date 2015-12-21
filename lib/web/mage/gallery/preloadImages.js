/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    /**
     * Loads images into browser's cash.
     * @param {Array <String>} array - List of sources of images.
     */
    var preloadImages = function (array) {
        var list,
            i,
            img,
            addToList;

        /**
         * Add image source to the list of loaded images
         * @param {Object} scope - this object
         */
        addToList = function (scope) {
            var index = list.indexOf(scope);

            if (index !== -1) {
                list.splice(index, 1);
            }
        };

        if (!preloadImages.list) {
            preloadImages.list = [];
        }
        list = preloadImages.list;

        for (i = 0; i < array.length; i++) {
            img = new Image();

            img.onload = addToList(this);

            list.push(img);
            img.src = array[i];
        }
    };

    return preloadImages;

});
