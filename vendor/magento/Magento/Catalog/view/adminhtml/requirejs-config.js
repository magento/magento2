/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

var config = {
    map: {
        '*': {
            categoryForm:       'Magento_Catalog/catalog/category/form',
            newCategoryDialog:  'Magento_Catalog/js/new-category-dialog',
            categoryTree:       'Magento_Catalog/js/category-tree',
            productGallery:     'Magento_Catalog/js/product-gallery',
            baseImage:          'Magento_Catalog/catalog/base-image-uploader',
            productAttributes:  'Magento_Catalog/catalog/product'
        }
    },
    deps: [
        "Magento_Catalog/catalog/product"
    ]
};