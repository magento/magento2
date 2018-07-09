<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Configuration\Item;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail;

/**
 * Resolves the product for a configured option item
 */
class ItemProductResolver implements \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     */
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/grouped_product_image';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getFinalProduct(
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) : \Magento\Catalog\Api\Data\ProductInterface {
        /**
         * Show grouped product thumbnail if it must be always shown according to the related setting in system config
         * or if child product thumbnail is not available
         */
        $config = $this->scopeConfig->getValue(
            \Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $childProduct = $item->getProduct();
        return $config == Thumbnail::OPTION_USE_PARENT_IMAGE ||
        (!$childProduct->getData('thumbnail') || $childProduct->getData('thumbnail') == 'no_selection')
            ?  $this->getParentProduct($item)
            : $childProduct;
    }

    /**
     * Get grouped product
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return \Magento\Catalog\Model\Product
     */
    private function getParentProduct(
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) : \Magento\Catalog\Model\Product {
        $option = $item->getOptionByCode('product_type');
        if ($option) {
            return $option->getProduct();
        }
        return $item->getProduct();
    }
}
