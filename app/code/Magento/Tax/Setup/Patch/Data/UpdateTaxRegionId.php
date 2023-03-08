<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup\Patch\Data;

use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Tax\Setup\TaxSetupFactory;

class UpdateTaxRegionId implements DataPatchInterface, PatchVersionInterface
{
    /**
     * UpdateTaxRegionId constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaFactory $searchCriteriaFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly TaxRateRepositoryInterface $taxRateRepository,
        private readonly SearchCriteriaFactory $searchCriteriaFactory,
        private readonly RegionFactory $regionFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        //Update the tax_region_id
        $taxRateList = $this->taxRateRepository->getList($this->searchCriteriaFactory->create());
        /** @var TaxRateInterface $taxRateData */
        foreach ($taxRateList->getItems() as $taxRateData) {
            $regionCode = $this->parseRegionFromTaxCode($taxRateData->getCode());
            if ($regionCode) {
                /** @var Region $region */
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
