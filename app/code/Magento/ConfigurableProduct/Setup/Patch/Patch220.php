<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Setup\Patch;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch220
{


    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $relatedProductTypes = explode(
            ',',
            $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'tier_price', 'apply_to')
        );
        $key = array_search(Configurable::TYPE_CODE, $relatedProductTypes);
        if ($key !== false) {
            unset($relatedProductTypes[$key]);
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'tier_price',
                'apply_to',
                implode(',', $relatedProductTypes)
            );
        }

        $setup->endSetup();

    }

}
