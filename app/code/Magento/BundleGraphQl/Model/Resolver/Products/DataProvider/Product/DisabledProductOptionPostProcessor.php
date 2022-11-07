<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionPostProcessorInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\SelectionProductsDisabledRequired;

/**
 * Remove bundle product from collection when all products in required option are disabled
 */
class DisabledProductOptionPostProcessor implements CollectionPostProcessorInterface
{
    /**
     * @var SelectionProductsDisabledRequired
     */
    private $selectionProductsDisabledRequired;

    /**
     * @param SelectionProductsDisabledRequired $selectionProductsDisabledRequired
     */
    public function __construct(
        SelectionProductsDisabledRequired $selectionProductsDisabledRequired
    ) {
        $this->selectionProductsDisabledRequired = $selectionProductsDisabledRequired;
    }

    /**
     * Remove bundle product from collection when all products in required option are disabled
     *
     * @param Collection $collection
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process(
        Collection $collection,
        array $attributeNames,
        ?ContextInterface $context = null
    ): Collection {
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        /** @var Product $product */
        foreach ($collection as $key => $product) {
            if ($product->getTypeId() !== Product\Type::TYPE_BUNDLE || $context === null) {
                continue;
            }
            $productId = (int)$product->getEntityId();
            $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();
            $productIdsDisabledRequired = $this->selectionProductsDisabledRequired->getChildProductIds(
                $productId,
                $websiteId
            );
            if ($productIdsDisabledRequired) {
                $collection->removeItemByKey($key);
            }
        }
        return $collection;
    }
}
