<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup\Patch;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch203 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param TaxRateRepositoryInterface $taxRateRepository
     */
    private $taxRateRepository;
    /**
     * @param RegionFactory $directoryRegionFactory
     */
    private $directoryRegionFactory;
    /**
     * @param TaxRateRepositoryInterface $taxRateRepository
     */
    private $taxRateRepository;

    /**
     * @param TaxRateRepositoryInterface $taxRateRepository @param RegionFactory $directoryRegionFactory@param TaxRateRepositoryInterface $taxRateRepository
     */
    public function __construct(TaxRateRepositoryInterface $taxRateRepository,
                                RegionFactory $directoryRegionFactory

        ,
                                TaxRateRepositoryInterface $taxRateRepository)
    {
        $this->taxRateRepository = $taxRateRepository;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->taxRateRepository = $taxRateRepository;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        /** @var TaxSetup $taxSetup */
        $taxSetup = $this->taxSetupFactory->create(['resourceName' => 'tax_setup', 'setup' => $setup]);

        $setup->startSetup();

        //Update the tax_region_id
        $taxRateList = $this->taxRateRepository->getList($this->searchCriteriaFactory->create());
        /** @var \Magento\Tax\Api\Data\TaxRateInterface $taxRateData */
        foreach ($taxRateList->getItems() as $taxRateData) {
            $regionCode = $this->parseRegionFromTaxCode($taxRateData->getCode());
            if ($regionCode) {
                /** @var \Magento\Directory\Model\Region $region */
                $region = $this->directoryRegionFactory->create();
                $region->loadByCode($regionCode, $taxRateData->getTaxCountryId());
                if ($taxRateData->getTaxPostcode() === null) {
                    $taxRateData->setTaxPostcode('*');
                }
                $taxRateData->setTaxRegionId($region->getRegionId());
                $this->taxRateRepository->save($taxRateData);
            }
        }
        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


    private function parseRegionFromTaxCode($taxCode
    )
    {
        $result = '';
        $parts = explode('-', $taxCode, 3);

        if (isset($parts[1])) {
            $result = $parts[1];
        }

        return $result;

    }
}
