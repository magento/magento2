<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Setup;

use Magento\Framework\Module\Manager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\GoogleShopping\Model\ConfigFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Configuration factory
     *
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * Modules manager
     *
     * @var Manager
     */
    private $moduleManager;

    /**
     * Init
     *
     * @param ConfigFactory $configFactory
     * @param Manager $moduleManager
     */
    public function __construct(ConfigFactory $configFactory, Manager $moduleManager)
    {
        $this->configFactory = $configFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($this->moduleManager->isEnabled('Magento_GoogleBase')) {
            $typesInsert = $setup->getConnection()->select()->from(
                $setup->getTable('googlebase_types'),
                ['type_id', 'attribute_set_id', 'target_country', 'category' => new \Zend_Db_Expr('NULL')]
            )->insertFromSelect(
                $setup->getTable('googleshopping_types')
            );

            $itemsInsert = $setup->getConnection()->select()->from(
                $setup->getTable('googlebase_items'),
                ['item_id', 'type_id', 'product_id', 'gbase_item_id', 'store_id', 'published', 'expires']
            )->insertFromSelect(
                $setup->getTable('googleshopping_items')
            );

            $attributes = '';
            foreach ($this->configFactory->create()->getAttributes() as $destAttribtues) {
                $keys = array_keys($destAttribtues);
                foreach ($keys as $code) {
                    $attributes .= "'{$code}',";
                }
            }
            $attributes = rtrim($attributes, ',');
            $attributesInsert = $setup->getConnection()->select()->from(
                $setup->getTable('googlebase_attributes'),
                [
                    'id',
                    'attribute_id',
                    'gbase_attribute' => new \Zend_Db_Expr(
                        "IF(gbase_attribute IN ({$attributes}), gbase_attribute, '')"
                    ),
                    'type_id'
                ]
            )->insertFromSelect(
                $setup->getTable('googleshopping_attributes')
            );

            $setup->run($typesInsert);
            $setup->run($attributesInsert);
            $setup->run($itemsInsert);
        }
    }
}
