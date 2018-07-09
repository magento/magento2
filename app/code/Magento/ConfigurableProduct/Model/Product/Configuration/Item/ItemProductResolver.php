<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Configuration\Item;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail;

/**
 * Resolves the product for a configured option item
 */
class ItemProductResolver implements \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
{
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/configurable_product_image';

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
         * Show parent product thumbnail if it must be always shown according to the related setting in system config
         * or if child thumbnail is not available
         */
        $parentItem = $item->getProduct();
        $config = $this->scopeConfig->getValue(
            \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $config == Thumbnail::OPTION_USE_PARENT_IMAGE
            || (!$this->getChildProduct($item)->getData('thumbnail')
            || $this->getChildProduct($item)->getData('thumbnail') == 'no_selection')
                ? $parentItem
                : $this->getChildProduct($item);
    }

    /**
     * Get item configurable child product
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return \Magento\Catalog\Model\Product
     */
    private function getChildProduct(
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) : \Magento\Catalog\Model\Product {
        $option = $item->getOptionByCode('simple_product');
        if ($option) {
            return $option->getProduct();
        }
        return $item->getProduct();
    }
}
