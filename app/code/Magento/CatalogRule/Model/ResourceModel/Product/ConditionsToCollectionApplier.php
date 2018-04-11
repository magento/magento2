<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\Exception\InputException;

/**
 * Class ConditionsToCollectionMapper
 * Applies catalog price rule conditions to product collection as filters
 *
 * @package Magento\CatalogRule\Model\ResourceModel\Product
 */
class ConditionsToCollectionApplier
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ConditionsToSearchCriteriaMapper
     */
    private $conditionsToSearchCriteriaMapper;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessor\AdvancedFilterProcessor
     */
    private $searchCriteriaProcessor;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\MappableConditionsProcessor
     */
    private $mappableConditionsProcessor;

    /**
     * @param \Magento\CatalogRule\Model\Rule\Condition\ConditionsToSearchCriteriaMapper $conditionsToSearchCriteriaMapper
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessor\AdvancedFilterProcessor $searchCriteriaProcessor
     * @param \Magento\CatalogRule\Model\Rule\Condition\MappableConditionsProcessor $mappableConditionsProcessor
     */
    public function __construct(
        \Magento\CatalogRule\Model\Rule\Condition\ConditionsToSearchCriteriaMapper $conditionsToSearchCriteriaMapper,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessor\AdvancedFilterProcessor $searchCriteriaProcessor,
        \Magento\CatalogRule\Model\Rule\Condition\MappableConditionsProcessor $mappableConditionsProcessor
    ) {
        $this->conditionsToSearchCriteriaMapper = $conditionsToSearchCriteriaMapper;
        $this->searchCriteriaProcessor = $searchCriteriaProcessor;
        $this->mappableConditionsProcessor = $mappableConditionsProcessor;
    }

    /**
     * Transforms catalog rule conditions to search criteria
     * and applies them on product collection
     *
     * @param Combine $conditions
     * @param ProductCollection $productCollection
     * @return ProductCollection
     * @throws InputException
     */
    public function applyConditionsToCollection(Combine $conditions, ProductCollection $productCollection)
    {
        // rebuild conditions to have only those that we know how to map them to product collection
        $mappableConditions = $this->mappableConditionsProcessor->rebuildConditionsTree($conditions);

        // transform conditions to search criteria
        $searchCriteria = $this->conditionsToSearchCriteriaMapper->mapConditionsToSearchCriteria($mappableConditions);

        $mappedProductCollection = clone $productCollection;

        // apply search criteria to new version of product collection
        $this->searchCriteriaProcessor->process($searchCriteria, $mappedProductCollection);

        return $mappedProductCollection;
    }
}
