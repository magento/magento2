<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as SearchCriteriaCollectionProcessor;

/**
 * Apply pre-defined catalog filtering
 *
 * {@inheritdoc}
 */
class CatalogProcessor implements CollectionProcessorInterface
{
    /** @var SearchCriteriaCollectionProcessor */
    private $collectionProcessor;

    /**
     * @param SearchCriteriaCollectionProcessor $collectionProcessor
     */
    public function __construct(
        SearchCriteriaCollectionProcessor $collectionProcessor
    ) {
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Process collection to add additional joins, attributes, and clauses to a category collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection {
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection;
    }
}
