<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Api\AbstractServiceCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Tax\Api\Data\TaxRuleInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;

/**
 * Tax rule collection for a grid backed by Services
 * @since 2.0.0
 */
class TaxRuleCollection extends AbstractServiceCollection
{
    /**
     * @var TaxRuleRepositoryInterface
     * @since 2.0.0
     */
    protected $ruleService;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param TaxRuleRepositoryInterface $ruleService
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        TaxRuleRepositoryInterface $ruleService
    ) {
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
        $this->ruleService = $ruleService;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->ruleService->getList($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            foreach ($searchResults->getItems() as $taxRule) {
                $this->_addItem($this->createTaxRuleCollectionItem($taxRule));
            }
            $this->_setIsLoaded();
        }
        return $this;
    }

    /**
     * Creates a collection item that represents a tax rule for the tax rules grid.
     *
     * @param TaxRuleInterface $taxRule Input data for creating the item.
     * @return \Magento\Framework\DataObject Collection item that represents a tax rule
     * @since 2.0.0
     */
    protected function createTaxRuleCollectionItem(TaxRuleInterface $taxRule)
    {
        $collectionItem = new \Magento\Framework\DataObject();
        $collectionItem->setTaxCalculationRuleId($taxRule->getId());
        $collectionItem->setCode($taxRule->getCode());
        /* should cast to string so that some optional fields won't be null on the collection grid pages */
        $collectionItem->setPriority((string)$taxRule->getPriority());
        $collectionItem->setPosition((string)$taxRule->getPosition());
        $collectionItem->setCalculateSubtotal($taxRule->getCalculateSubtotal() ? '1' : '0');
        $collectionItem->setCustomerTaxClasses($taxRule->getCustomerTaxClassIds());
        $collectionItem->setProductTaxClasses($taxRule->getProductTaxClassIds());
        $collectionItem->setTaxRatesCodes($taxRule->getTaxRatesCodes());
        $collectionItem->setTaxRates($taxRule->getTaxRateIds());
        return $collectionItem;
    }
}
