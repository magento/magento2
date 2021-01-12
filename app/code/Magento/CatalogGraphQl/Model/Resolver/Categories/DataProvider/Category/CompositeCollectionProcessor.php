<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Composite collection processor
 *
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
     * Process collection to add additional joins, attributes, and clauses to a category collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection {
        foreach ($this->collectionProcessors as $collectionProcessor) {
            $collection = $collectionProcessor->process($collection, $searchCriteria, $attributeNames, $context);
        }

        return $collection;
    }
}
