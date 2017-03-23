/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            categoryForm:       'Magento_Catalog/catalog/category/form',
            newCategoryDialog:  'Magento_Catalog/js/new-category-dialog',
            categoryTree:       'Magento_Catalog/js/category-tree',
            productGallery:     'Magento_Catalog/js/product-gallery',
            baseImage:          'Magento_Catalog/catalog/base-image-uploader',
            productAttributes:  'Magento_Catalog/catalog/product-attributes'
        }
    },
    deps: [
        'Magento_Catalog/catalog/product'
    ]
};
