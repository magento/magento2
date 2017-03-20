<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Setup;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Tax Setup Resource Model
 */
class TaxSetup
{
    /**
     * @var \Magento\Sales\Setup\SalesSetup
     */
    protected $salesSetup;

    /**
     * Product type config
     *
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * Init
     *
     * @param ModuleDataSetupInterface $setup
     * @param SalesSetupFactory $salesSetupFactory
     * @param ConfigInterface $productTypeConfig
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        SalesSetupFactory $salesSetupFactory,
        ConfigInterface $productTypeConfig
    ) {
        $this->salesSetup = $salesSetupFactory->create(['resourceName' => 'tax_setup', 'setup' => $setup]);
        $this->productTypeConfig = $productTypeConfig;
    }

    /**
     * Get taxable product types
     *
     * @return array
     */
    public function getTaxableItems()
    {
        return $this->productTypeConfig->filter('taxable');
    }

    /**
     * Add entity attribute.
     *
     * @param int|string $entityTypeId
     * @param string $code
     * @param array $attr
     * @return $this
     */
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        //Delegate
        return $this->salesSetup->addAttribute($entityTypeId, $code, $attr);
    }

    /**
     * Update Attribute data and Attribute additional data.
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @param string $field
     * @param mixed $value
     * @param int $sortOrder
     * @return $this
     */
    public function updateAttribute($entityTypeId, $id, $field, $value = null, $sortOrder = null)
    {
        //Delegate
        return $this->salesSetup->updateAttribute($entityTypeId, $id, $field, $value, $sortOrder);
    }
}
