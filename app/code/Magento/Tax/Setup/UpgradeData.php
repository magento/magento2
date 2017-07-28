<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Tax setup factory
     *
     * @var TaxSetupFactory
     * @since 2.0.0
     */
    private $taxSetupFactory;

    /**
     * Tax rate repository
     *
     * @var TaxRateRepositoryInterface
     * @since 2.2.0
     */
    private $taxRateRepository;

    /**
     * @var SearchCriteriaFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var RegionFactory
     * @since 2.2.0
     */
    private $directoryRegionFactory;

    /**
     * Init
     *
     * @param TaxSetupFactory $taxSetupFactory
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaFactory $searchCriteriaFactory
     * @param RegionFactory $directoryRegionFactory
     * @since 2.0.0
     */
    public function __construct(
        TaxSetupFactory $taxSetupFactory,
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaFactory $searchCriteriaFactory,
        RegionFactory $directoryRegionFactory
    ) {
        $this->taxSetupFactory = $taxSetupFactory;
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var TaxSetup $taxSetup */
        $taxSetup = $this->taxSetupFactory->create(['resourceName' => 'tax_setup', 'setup' => $setup]);

        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
             //Update the tax_class_id attribute in the 'catalog_eav_attribute' table
            $taxSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'tax_class_id',
                'is_visible_in_advanced_search',
                false
            );
        }
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            //Update the tax_region_id
            $taxRateList = $this->taxRateRepository->getList($this->searchCriteriaFactory->create());
            /** @var \Magento\Tax\Api\Data\TaxRateInterface $taxRateData */
            foreach ($taxRateList->getItems() as $taxRateData) {
                if (!empty($taxRateData->getData('percentage_rate'))) {
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
        }
        $setup->endSetup();
    }

    /**
     * Parse region code from tax code
     *
     * @param string $taxCode
     * @return string
     * @since 2.2.0
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
