<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as SearchCriteriaApplier;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Apply search criteria data to passed in collection.
 *
 * {@inheritdoc}
 */
class SearchCriteriaProcessor implements CollectionProcessorInterface
{
    /**
     * @var SearchCriteriaApplier
     */
    private $searchCriteriaApplier;

    /**
     * @param SearchCriteriaApplier $searchCriteriaApplier
     */
    public function __construct(SearchCriteriaApplier $searchCriteriaApplier)
    {
        $this->searchCriteriaApplier = $searchCriteriaApplier;
    }

    /**
     * Process collection to add additional joins, attributes, and clauses to a product collection.
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
        $this->searchCriteriaApplier->process($searchCriteria, $collection);

        return $collection;
    }
}
