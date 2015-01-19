<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Core\Model\EntityFactory;
use Magento\Framework\Api\AbstractServiceCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Tax\Api\Data\TaxRateInterface as TaxRate;

/**
 * Tax rate collection for a grid backed by Services
 */

class TaxRateCollection extends AbstractServiceCollection
{
    /**
     * @var TaxRateRepositoryInterface
     */
    protected $taxRateRepository;

    /**
     * @var \Magento\Tax\Model\Calculation\Rate\Converter
     */
    protected $rateConverter;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param TaxRateRepositoryInterface $rateService
     * @param \Magento\Tax\Model\Calculation\Rate\Converter $rateConverter
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        TaxRateRepositoryInterface $rateService,
        \Magento\Tax\Model\Calculation\Rate\Converter $rateConverter
    ) {
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
        $this->taxRateRepository = $rateService;
        $this->rateConverter = $rateConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->taxRateRepository->getList($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            foreach ($searchResults->getItems() as $taxRate) {
                $this->_addItem($this->createTaxRateCollectionItem($taxRate));
            }
            $this->_setIsLoaded();
        }
        return $this;
    }

    /**
     * Creates a collection item that represents a tax rate for the tax rates grid.
     *
     * @param TaxRate $taxRate Input data for creating the item.
     * @return \Magento\Framework\Object Collection item that represents a tax rate
     */
    protected function createTaxRateCollectionItem(TaxRate $taxRate)
    {
        $collectionItem = new \Magento\Framework\Object();
        $collectionItem->setTaxCalculationRateId($taxRate->getId());
        $collectionItem->setCode($taxRate->getCode());
        $collectionItem->setTaxCountryId($taxRate->getTaxCountryId());
        $collectionItem->setTaxRegionId($taxRate->getTaxRegionId());
        $collectionItem->setRegionName($taxRate->getRegionName());
        $collectionItem->setTaxPostcode($taxRate->getTaxPostcode());
        $collectionItem->setRate($taxRate->getRate());
        $collectionItem->setTitles($this->rateConverter->createTitleArrayFromServiceObject($taxRate));

        if ($taxRate->getZipTo() != null && $taxRate->getZipFrom() != null) {
            /* must be a "1" for existing code (e.g. JavaScript) to work */
            $collectionItem->setZipIsRange("1");
            $collectionItem->setZipFrom($taxRate->getZipFrom());
            $collectionItem->setZipTo($taxRate->getZipTo());
        } else {
            $collectionItem->setZipIsRange(null);
            $collectionItem->setZipFrom(null);
            $collectionItem->setZipTo(null);
        }

        return $collectionItem;
    }
}
