<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch215
{


    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
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


        $this->disallowUsingHtmlForProductName($setup);


        $setup->endSetup();

    }

    private function disallowUsingHtmlForProductName(ModuleDataSetupInterface $setup
    )
    {
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attribute = $categorySetup->getAttribute($entityTypeId, 'name');

        $setup->getConnection()
            ->update(
                $setup->getTable('catalog_eav_attribute'),
                ['is_html_allowed_on_front' => 0],
                $setup->getConnection()->quoteInto('attribute_id = ?', $attribute['attribute_id'])
            );

    }
}
