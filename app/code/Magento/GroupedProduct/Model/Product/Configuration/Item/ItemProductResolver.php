<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Product\Configuration\Item;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;

/**
 * {@inheritdoc}
 */
class ItemProductResolver implements ItemResolverInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     */
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/grouped_product_image';

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
    public function getFinalProduct(ItemInterface $item): ProductInterface
=======
    public function getFinalProduct(ItemInterface $item) : ProductInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        /**
         * Show grouped product thumbnail if it must be always shown according to the related setting in system config
         * or if child product thumbnail is not available.
         */
<<<<<<< HEAD
        $childProduct = $item->getProduct();
        $finalProduct = $childProduct;
        $parentProduct = $this->getParentProduct($item);

=======

        $childProduct = $item->getProduct();
        $finalProduct = $childProduct;
        $parentProduct = $this->getParentProduct($item);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $finalProduct;
    }

    /**
     * Get grouped product.
     *
     * @param ItemInterface $item
     * @return Product
     */
    private function getParentProduct(ItemInterface $item) : Product
    {
        $option = $item->getOptionByCode('product_type');
        $product = $item->getProduct();
<<<<<<< HEAD

        if ($option) {
            $product = $option->getProduct();
        }

=======
        if ($option) {
            $product = $option->getProduct();
        }
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $product;
    }
}
