<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Processing applied to the collection after load
 */
interface CollectionPostProcessorInterface
{
    /**
     * Apply processing to loaded product collection
     *
     * @param Collection $collection
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     */
    public function process(
        Collection $collection,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection;
}
