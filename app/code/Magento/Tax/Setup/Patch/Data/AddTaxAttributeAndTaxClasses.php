<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\TaxClass\Source\Product as TaxClassSourceProduct;
use Magento\Tax\Setup\TaxSetup;
use Magento\Tax\Setup\TaxSetupFactory;

/**
 * Class AddTacAttributeAndTaxClasses
 * @package Magento\Tax\Setup\Patch
 */
class AddTaxAttributeAndTaxClasses implements DataPatchInterface, PatchVersionInterface
{
    /**
     * AddTacAttributeAndTaxClasses constructor.
     *
     * @param TaxSetupFactory $taxSetupFactory
     * @param RegionFactory $directoryRegionFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly TaxSetupFactory $taxSetupFactory,
        private readonly RegionFactory $directoryRegionFactory,
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var TaxSetup $taxSetup */
        $taxSetup = $this->taxSetupFactory->create(['resourceName' => 'tax_setup', 'setup' => $this->moduleDataSetup]);

        /**
         * Add tax_class_id attribute to the 'eav_attribute' table
         */
        $taxSetup->addAttribute(
            Product::ENTITY,
            'tax_class_id',
            [
                'group' => 'Product Details',
                'sort_order' => 40,
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Tax Class',
                'input' => 'select',
                'class' => '',
                'source' => TaxClassSourceProduct::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '2',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => implode(',', $taxSetup->getTaxableItems()),
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
        );
        /**
         * install tax classes
         */
        $data = [
            [
                'class_id' => 2,
                'class_name' => 'Taxable Goods',
                'class_type' => ClassModel::TAX_CLASS_TYPE_PRODUCT,
            ],
            [
                'class_id' => 3,
                'class_name' => 'Retail Customer',
                'class_type' => ClassModel::TAX_CLASS_TYPE_CUSTOMER
            ],
        ];
        foreach ($data as $row) {
            $this->moduleDataSetup->getConnection()->insertForce(
                $this->moduleDataSetup->getTable('tax_class'),
                $row
            );
        }
        /**
         * install tax calculation rates
         */
        /** @var Region $region */
        $region = $this->directoryRegionFactory->create();
        $data = [
            [
                'tax_calculation_rate_id' => 1,
                'tax_country_id' => 'US',
                'tax_region_id' => $region->loadByCode('CA', 'US')->getRegionId(),
                'tax_postcode' => '*',
                'code' => 'US-CA-*-Rate 1',
                'rate' => '8.2500',
            ],
            [
                'tax_calculation_rate_id' => 2,
                'tax_country_id' => 'US',
                'tax_region_id' => $region->loadByCode('NY', 'US')->getRegionId(),
                'tax_postcode' => '*',
                'code' => 'US-NY-*-Rate 1',
                'rate' => '8.3750'
            ],
        ];
        foreach ($data as $row) {
            $this->moduleDataSetup->getConnection()->insertForce(
                $this->moduleDataSetup->getTable('tax_calculation_rate'),
                $row
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
