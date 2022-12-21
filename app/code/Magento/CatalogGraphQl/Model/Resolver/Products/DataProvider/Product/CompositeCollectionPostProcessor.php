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
 * {@inheritdoc}
 */
class CompositeCollectionPostProcessor implements CollectionPostProcessorInterface
{
    /**
     * @var CollectionPostProcessorInterface[]
     */
    private $collectionPostProcessors;

    /**
     * @param CollectionProcessorInterface[] $collectionPostProcessors
     */
    public function __construct(array $collectionPostProcessors = [])
    {
        $this->collectionPostProcessors = $collectionPostProcessors;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Collection $collection, array $attributeNames, ContextInterface $context = null): Collection
    {
        foreach ($this->collectionPostProcessors as $collectionPostProcessor) {
            $collection = $collectionPostProcessor->process($collection, $attributeNames, $context);
        }
        return $collection;
    }
}
