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
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;
use Magento\Tax\Setup\TaxSetupFactory;

class UpdateTaxRegionId implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

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
     * @param ResourceConnection $resourceConnection
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaFactory $searchCriteriaFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaFactory $searchCriteriaFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();

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
        $this->resourceConnection->getConnection()->endSetup();
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
