<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Tax setup factory
     *
     * @var TaxSetupFactory
     */
    private $taxSetupFactory;

    /**
     * Init
     *
     * @param TaxSetupFactory $taxSetupFactory
     */
    public function __construct(TaxSetupFactory $taxSetupFactory)
    {
        $this->taxSetupFactory = $taxSetupFactory;
    }

    /**
     * {@inheritdoc}
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

        $setup->endSetup();
    }
}
