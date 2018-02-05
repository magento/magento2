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
class Patch201
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


        $select = $setup->getConnection()->select()
            ->from(
                $setup->getTable('catalog_product_entity_group_price'),
                [
                    'entity_id',
                    'all_groups',
                    'customer_group_id',
                    new \Zend_Db_Expr('1'),
                    'value',
                    'website_id'
                ]
            );
        $select = $setup->getConnection()->insertFromSelect(
            $select,
            $setup->getTable('catalog_product_entity_tier_price'),
            [
                'entity_id',
                'all_groups',
                'customer_group_id',
                'qty',
                'value',
                'website_id'
            ]
        );
        $setup->getConnection()->query($select);

        $categorySetupManager = $this->categorySetupFactory->create();
        $categorySetupManager->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'group_price');


        $setup->endSetup();

    }

}
