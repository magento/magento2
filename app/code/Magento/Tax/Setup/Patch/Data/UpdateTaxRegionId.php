<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup\Patch\Data;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Tax\Setup\TaxSetupFactory;

class UpdateTaxRegionId implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
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
    private $regionFactory;

    /**
     * UpdateTaxRegionId constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaFactory $searchCriteriaFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaFactory $searchCriteriaFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        //Update the tax_region_id
        $taxRateList = $this->taxRateRepository->getList($this->searchCriteriaFactory->create());
        /** @var \Magento\Tax\Api\Data\TaxRateInterface $taxRateData */
        foreach ($taxRateList->getItems() as $taxRateData) {
            $regionCode = $this->parseRegionFromTaxCode($taxRateData->getCode());
            if ($regionCode) {
                /** @var \Magento\Directory\Model\Region $region */
                $region = $this->regionFactory->create();
                $region->loadByCode($regionCode, $taxRateData->getTaxCountryId());
                if ($taxRateData->getTaxPostcode() === null) {
                    $taxRateData->setTaxPostcode('*');
                }
                $taxRateData->setTaxRegionId($region->getRegionId());
                $this->taxRateRepository->save($taxRateData);
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateTaxClassAttributeVisibility::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.3';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Parse region from tax code.
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
