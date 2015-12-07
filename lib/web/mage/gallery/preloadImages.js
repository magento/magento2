/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {

	/**
     * Loads images into browser's cash.
     * @param {Array <String>} array - List of sources of images.
     */
    var preloadImages = function (array) {
        if (!preloadImages.list) {
            preloadImages.list = [];
        }
        var list = preloadImages.list;

        for (var i = 0; i < array.length; i++) {
            var img = new Image();

            img.onload = function() {            	
                var index = list.indexOf(this);

                if (index !== -1) {
                    list.splice(index, 1);
                }
            }

            list.push(img);
            img.src = array[i];
        }
    };

    return preloadImages;

});
