<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product\Configuration\Item;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\ScopeInterface;

/**
 * Resolves the product from a configured item.
 */
class ItemProductResolver implements ItemResolverInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     */
    public const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/configurable_product_image';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the final product from a configured item by product type and selection.
     *
     * @param ItemInterface $item
     * @return ProductInterface
     */
    public function getFinalProduct(ItemInterface $item): ProductInterface
    {
        /**
         * Show parent product thumbnail if it must be always shown according to the related setting in system config
         * or if child thumbnail is not available.
         */
        $finalProduct = $item->getProduct();
        $childProduct = $this->getChildProduct($item);
        if ($childProduct !== null && $this->isUseChildProduct($childProduct)) {
            $finalProduct = $childProduct;
        }
        return $finalProduct;
    }

    /**
     * Get item configurable child product.
     *
     * @param ItemInterface $item
     * @return Product | null
     */
    private function getChildProduct(ItemInterface $item): ?Product
    {
        /** @var \Magento\Quote\Model\Quote\Item\Option $option */
        $option = $item->getOptionByCode('simple_product');
        return $option ? $option->getProduct() : null;
    }

    /**
     * Is need to use child product
     *
     * @param Product $childProduct
     * @return bool
     */
    private function isUseChildProduct(Product $childProduct): bool
    {
        $configValue = $this->scopeConfig->getValue(
            self::CONFIG_THUMBNAIL_SOURCE,
            ScopeInterface::SCOPE_STORE
        );
        $childThumb = $childProduct->getData('thumbnail');
        return $configValue !== Thumbnail::OPTION_USE_PARENT_IMAGE
            && $childThumb !== null
            && $childThumb !== 'no_selection';
    }
}
