<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Data\Helper\PostHelper;

/**
 * Collect information about all urls, that needed to render product on front
 * Also collect information about add-to-cart, add-to-compare, add-to-wishlist buttons
 * @since 2.2.0
 */
class Url implements ProductRenderCollectorInterface
{
    /** Compare Data key */
    const KEY_COMPARE_URL_POST_DATA = "compare_url_post_data";

    /** Add to cart url key post data */
    const KEY_ADD_TO_CART_URL_POST_DATA = "add_to_cart_url_post_data";

    /** Add to cart url key */
    const KEY_ADD_TO_CART_URL = "add_to_cart_url";

    /** Product Url */
    const KEY_URL = "url";

    /** Has Required options key */
    const KEY_HAS_REQUIRED_OPTIONS = "has_required_options";

    /**
     * @var AbstractProduct
     * @since 2.2.0
     */
    private $abstractProduct;

    /**
     * @var Compare
     * @since 2.2.0
     */
    private $compare;

    /**
     * @var PostHelper
     * @since 2.2.0
     */
    private $postHelper;

    /**
     * @var ButtonInterfaceFactory
     * @since 2.2.0
     */
    private $buttonFactory;

    /**
     * @param AbstractProduct $abstractProduct
     * @param Compare $compare
     * @param PostHelper $postHelper
     * @param ButtonInterfaceFactory $buttonFactory
     * @since 2.2.0
     */
    public function __construct(
        AbstractProduct $abstractProduct,
        Compare $compare,
        PostHelper $postHelper,
        ButtonInterfaceFactory $buttonFactory
    ) {
        $this->abstractProduct = $abstractProduct;
        $this->compare = $compare;
        $this->postHelper = $postHelper;
        $this->buttonFactory = $buttonFactory;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $addToCart = $productRender->getAddToCartButton();
        $addToCompare = $productRender->getAddToCartButton();

        if (!$addToCart) {
            $addToCart = $this->buttonFactory->create();
        }

        if (!$addToCompare) {
            $addToCompare = $this->buttonFactory->create();
        }

        $addToCart->setPostData(
            $this->postHelper->getPostData(
                $this->abstractProduct->getAddToCartUrl($product, ['useUencPlaceholder' => true]),
                ['product' => $product->getId(),
                    \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => "%uenc%"
                ]
            )
        );
        $addToCart->setRequiredOptions((bool) $product->getData('has_options'));
        $addToCart->setUrl(
            $this->abstractProduct
                ->getAddToCartUrl($product, ['useUencPlaceholder' => true])
        );

        $addToCompare->setUrl($this->compare->getPostDataParams($product));

        $productRender->setAddToCartButton($addToCart);
        $productRender->setAddToCompareButton($addToCompare);
        $productRender->setUrl($product->getProductUrl());
    }
}
