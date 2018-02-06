<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param TaxSetupFactory $taxSetupFactory
     */
    public function __construct(TaxSetupFactory $taxSetupFactory)
    {
        $this->taxSetupFactory = $taxSetupFactory;
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

        //Update the tax_class_id attribute in the 'catalog_eav_attribute' table
        $taxSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'tax_class_id',
            'is_visible_in_advanced_search',
            false
        );
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


}
