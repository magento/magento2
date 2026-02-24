<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
 */
class Url implements ProductRenderCollectorInterface
{
    /**
     * @var AbstractProduct
     */
    private $abstractProduct;

    /**
     * @var Compare
     */
    private $compare;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var ButtonInterfaceFactory
     */
    private $buttonFactory;

    /**
     * @param AbstractProduct $abstractProduct
     * @param Compare $compare
     * @param PostHelper $postHelper
     * @param ButtonInterfaceFactory $buttonFactory
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
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $addToCart = $productRender->getAddToCartButton();
        $addToCompare = $productRender->getAddToCompareButton();

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
