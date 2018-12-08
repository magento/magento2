<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * {@inheritdoc}
 */
class CompositeCollectionProcessor implements CollectionProcessorInterface
{
    /**
     * @var CollectionProcessorInterface[]
     */
    private $collectionProcessors;

    /**
     * @param CollectionProcessorInterface[] $collectionProcessors
     */
    public function __construct(array $collectionProcessors = [])
    {
        $this->collectionProcessors = $collectionProcessors;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ): Collection {
        foreach ($this->collectionProcessors as $collectionProcessor) {
            $collection = $collectionProcessor->process($collection, $searchCriteria, $attributeNames);
        }

        return $collection;
    }
}
