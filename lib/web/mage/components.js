/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

define([], function() {
    var components = {
        productListToolbarForm: 'Magento_Catalog/js/product/list/toolbar',
        productSummary: 'Magento_Bundle/js/product-summary',
        toggleAdvanced: 'mage/toggle',
        translateInline: 'mage/translate-inline',

        //Authorizenet\view\frontend\templates\js\components.phtml
        authorizenetAuthenticate: 'Magento_Authorizenet/authorizenet-authenticate',

        //Bundle\view\frontend\templates\js\components.phtml
        sticky: 'mage/sticky',
        bundleOption: 'Magento_Bundle/bundle',
        slide: 'Magento_Bundle/js/slide',

        //Captcha\view\frontend\templates\js\components.phtml
        captcha: 'Magento_Captcha/captcha',

        //Catalog\view\frontend\templates\js\components.phtml
        tabs: 'mage/tabs',
        catalogSearch: 'Magento_CatalogSearch/form-mini',
        compareItems: 'Magento_Catalog/js/compare',
        compareList: 'Magento_Catalog/js/list',
        fileOption: 'Magento_Catalog/js/file-option',
        relatedProducts: 'Magento_Catalog/js/related-products',
        upsellProducts: 'Magento_Catalog/js/upsell-products',
        discountCode: 'Magento_Checkout/js/discount-codes',
        catalogGallery: 'Magento_Catalog/js/gallery',
        orderOverview: 'Magento_Checkout/js/overview',
        rowBuilder: 'Magento_Theme/js/row-builder',
        address: 'Magento_Customer/address',
        priceOption: 'Magento_Catalog/js/price-option',
        requireCookie: 'Magento_Core/js/require-cookie',
        addToCart: 'Magento_Msrp/js/msrp',
        tierPrice: 'Magento_Catalog/js/tier-price',
        dateOption: 'Magento_Catalog/js/date-option',
        zoom: 'mage/zoom',
        gallery: 'mage/gallery',
        galleryFullScreen: 'mage/gallery-fullscreen',

        //Checkout\view\frontend\templates\js\components.phtml
        paymentAuthentication: 'Magento_Checkout/js/payment-authentication',
        collapsible: 'mage/collapsible',
        dropdownDialog: 'mage/dropdown',
        accordion: 'mage/accordion',
        checkoutBalance: 'Magento_Customer/js/checkout-balance',
        shoppingCart: 'Magento_Checkout/js/shopping-cart',
        regionUpdater: 'Magento_Checkout/js/region-updater',
        creditCardType: 'Magento_Payment/cc-type',
        loader: 'mage/loader',
        tooltip: 'mage/tooltip',
        opcOrderReview: 'Magento_Checkout/js/opc-order-review',
        sidebar: 'Magento_Checkout/js/sidebar',
        payment: 'Magento_Checkout/js/payment',

        //ConfigurableProduct\view\frontend\templates\js\components.phtml
        configurable: 'Magento_ConfigurableProduct/js/configurable',

        //Customer\view\frontend\templates\js\components.phtml
        setPassword: 'Magento_Customer/set-password',

        //Downloadable\view\frontend\templates\js\components.phtml
        downloadable: 'Magento_Downloadable/downloadable',

        //Multishipping\view\frontend\templates\js\components.phtml
        multiShipping: 'Magento_Multishipping/js/multi-shipping',

        //Newsletter\view\frontend\templates\js\components.phtml
        newsletter: 'Magento_Newsletter/newsletter',

        //PageCache\view\frontend\templates\js\components.phtml
        formKey: 'Magento_PageCache/js/form-key',
        pageCache: 'Magento_PageCache/js/page-cache',

        //Paypal\view\frontend\templates\js\components.phtml
        opcheckoutPaypalIframe: 'Magento_Paypal/js/opcheckout',
        orderReview: 'Magento_Paypal/order-review',

        //Reports\view\frontend\templates\js\components.phtml
        recentlyViewedProducts: 'Magento_Reports/js/recently-viewed',

        //Sales\view\frontend\templates\js\components.phtml
        extraOptions: 'Magento_GiftMessage/extra-options',
        giftMessage: 'Magento_Sales/gift-message',
        giftOptions: 'Magento_GiftMessage/gift-options',
        ordersReturns: 'Magento_Sales/orders-returns',

        //Theme\view\frontend\templates\js\components.phtml
        deletableItem: 'mage/deletable-item',
        itemTable: 'mage/item-table',
        fieldsetControls: 'mage/fieldset-controls',
        fieldsetResetControl: 'mage/fieldset-controls',
        redirectUrl: 'mage/redirect-url',
        cookieBlock: 'Magento_Theme/js/notices',

        //Wishlist\view\frontend\templates\js\components.phtml
        wishlist: 'Magento_Wishlist/wishlist',
        addToWishlist: 'Magento_Wishlist/js/add-to-wishlist',
        wishlistSearch: 'Magento_Wishlist/js/search',

        loaderAjax: 'mage/loader',
        directpost: 'Magento_Authorizenet/js/direct-post',
        menu: 'mage/menu',
        popupWindow: 'mage/popup-window',
        centinelAuthenticate: 'Magento_Centinel/centinel-authenticate'
    };

    return components;
});
