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
     * {@inheritdoc}
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ): Collection {
        $this->searchCriteriaApplier->process($searchCriteria, $collection);

        return $collection;
    }
}
