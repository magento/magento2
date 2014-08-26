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

namespace Magento\Tax\Service\V1\Collection;

use Magento\Core\Model\EntityFactory;
use Magento\Framework\Service\AbstractServiceCollection;
use Magento\Framework\Service\V1\Data\FilterBuilder;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Service\V1\TaxRateServiceInterface;
use Magento\Tax\Service\V1\Data\TaxRate;
use Magento\Framework\Service\V1\Data\SortOrderBuilder;

/**
 * Tax rate collection for a grid backed by Services
 */
class TaxRateCollection extends AbstractServiceCollection
{
    /**
     * @var TaxRateServiceInterface
     */
    protected $rateService;

    /**
     * @var Converter
     */
    protected $rateConverter;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TaxRateServiceInterface $rateService
     * @param Converter $rateConverter
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        TaxRateServiceInterface $rateService,
        Converter $rateConverter
    ) {
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
        $this->rateService = $rateService;
        $this->rateConverter = $rateConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->rateService->searchTaxRates($searchCriteria);
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
        $collectionItem->setTaxCountryId($taxRate->getCountryId());
        $collectionItem->setTaxRegionId($taxRate->getRegionId());
        $collectionItem->setRegionName($taxRate->getRegionName());
        $collectionItem->setTaxPostcode($taxRate->getPostcode());
        $collectionItem->setRate($taxRate->getPercentageRate());
        $collectionItem->setTitles($this->rateConverter->createTitleArrayFromServiceObject($taxRate));

        if ($taxRate->getZipRange() != null) {
            $zipRange = $taxRate->getZipRange();

            /* must be a "1" for existing code (e.g. JavaScript) to work */
            $collectionItem->setZipIsRange("1");
            $collectionItem->setZipFrom($zipRange->getFrom());
            $collectionItem->setZipTo($zipRange->getTo());
        } else {
            $collectionItem->setZipIsRange(null);
            $collectionItem->setZipFrom(null);
            $collectionItem->setZipTo(null);
        }

        return $collectionItem;
    }
}
