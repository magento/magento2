<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Model;

use Magento\Core\Model\EntityFactory;
use Magento\Framework\Api\AbstractServiceCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\Data\TaxRateInterface as TaxRate;
use Magento\Framework\Api\SortOrderBuilder;

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
     * @param TaxRateRepositoryInterface $rateService
     * @param \Magento\Tax\Model\Calculation\Rate\Converter $rateConverter
     * @param SortOrderBuilder $sortOrderBuilder
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
