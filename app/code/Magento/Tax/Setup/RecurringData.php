<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;

/**
 * Update installed tax region codes
 */
class RecurringData implements InstallDataInterface
{
    /**
     * Tax rate repository
     *
     * @var TaxRateRepositoryInterface
     */
    private $taxRateRepository;

    /**
     * @var SearchCriteriaFactory
     */

    private $searchCriteriaFactory;

    /**
     * @var RegionFactory
     */
    private $directoryRegionFactory;

    /**
     * Init
     *
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaFactory $searchCriteriaFactory
     * @param RegionFactory $directoryRegionFactory
     */
    public function __construct(
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaFactory $searchCriteriaFactory,
        RegionFactory $directoryRegionFactory
    ) {
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $taxRateList = $this->taxRateRepository->getList($this->searchCriteriaFactory->create());
        /** @var \Magento\Tax\Api\Data\TaxRateInterface $taxRateData */
        foreach ($taxRateList->getItems() as $taxRateData) {
            $regionCode = $this->parseRegionFromTaxCode($taxRateData->getCode());
            if ($regionCode) {
                /** @var \Magento\Directory\Model\Region $region */
                $region = $this->directoryRegionFactory->create();
                $region->loadByCode($regionCode, $taxRateData->getTaxCountryId());
                $taxRateData->setTaxRegionId($region->getRegionId());
                $this->taxRateRepository->save($taxRateData);
            }
        }
    }

    /**
     * Parse region code from tax code
     *
     * @param string $taxCode
     * @return string
     */
    private function parseRegionFromTaxCode($taxCode)
    {
        $result = '';
        $parts = explode('-', $taxCode, 3);

        if (isset($parts[1])) {
            $result = $parts[1];
        }

        return $result;
    }
}
