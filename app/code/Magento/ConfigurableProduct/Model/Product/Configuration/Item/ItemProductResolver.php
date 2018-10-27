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

/**
 * {@inheritdoc}
 */
class ItemProductResolver implements ItemResolverInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     */
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/configurable_product_image';

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
     * {@inheritdoc}
     */
<<<<<<< HEAD
    public function getFinalProduct(ItemInterface $item) : ProductInterface
=======
    public function getFinalProduct(ItemInterface $item): ProductInterface
>>>>>>> upstream/2.2-develop
    {
        /**
         * Show parent product thumbnail if it must be always shown according to the related setting in system config
         * or if child thumbnail is not available.
         */
        $parentProduct = $item->getProduct();
        $finalProduct = $parentProduct;
        $childProduct = $this->getChildProduct($item);
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
        if ($childProduct !== $parentProduct) {
            $configValue = $this->scopeConfig->getValue(
                self::CONFIG_THUMBNAIL_SOURCE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $childThumb = $childProduct->getData('thumbnail');
            $finalProduct =
                ($configValue == Thumbnail::OPTION_USE_PARENT_IMAGE) || (!$childThumb || $childThumb == 'no_selection')
                    ? $parentProduct
                    : $childProduct;
        }
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
        return $finalProduct;
    }

    /**
     * Get item configurable child product.
     *
     * @param ItemInterface $item
     * @return Product
     */
<<<<<<< HEAD
    private function getChildProduct(ItemInterface $item) : Product
    {
        $option = $item->getOptionByCode('simple_product');
        $product = $item->getProduct();
        if ($option) {
            $product = $option->getProduct();
        }
=======
    private function getChildProduct(ItemInterface $item): Product
    {
        $option = $item->getOptionByCode('simple_product');
        $product = $item->getProduct();

        if ($option) {
            $product = $option->getProduct();
        }

>>>>>>> upstream/2.2-develop
        return $product;
    }
}
