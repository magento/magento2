/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

var config = {
    map: {
        '*': {
            wishlist:       'Magento_Wishlist/js/wishlist',
            addToWishlist:  'Magento_Wishlist/js/add-to-wishlist',
            wishlistSearch: 'Magento_Wishlist/js/search'
        }
    },
    config: {
        mixins: {
            'Magento_Wishlist/js/view/wishlist': {
                'Magento_Wishlist/js/view/wishlist-mixin': true
            }
        }
    }
};
