<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('catalog_product_entity_group_price'),
                    [
                        'value_id',
                        'entity_id',
                        'all_groups',
                        'customer_group_id',
                        new \Zend_Db_Expr('1'),
                        'value',
                        'website_id'
                    ]
                );
            $setup->getConnection()->insertFromSelect(
                $select,
                $setup->getTable('catalog_product_entity_group_price'),
                [
                    'value_id',
                    'entity_id',
                    'all_groups',
                    'customer_group_id',
                    'qty',
                    'value',
                    'website_id'
                ]
            );
            $categorySetupManager = $this->categorySetupFactory->create();
            $categorySetupManager->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'group_price');
        }
        $setup->endSetup();
    }
}
