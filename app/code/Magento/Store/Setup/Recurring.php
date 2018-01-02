<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Setup;

use Magento\Catalog\Helper\DefaultCategory;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var DefaultCategory
     */
    private $defaultCategory;

    /**
     * @deprecated 100.1.0
     * @return DefaultCategory
     */
    private function getDefaultCategory()
    {
        if ($this->defaultCategory === null) {
            $this->defaultCategory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(DefaultCategory::class);
        }
        return $this->defaultCategory;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $connection = $installer->getConnection();
        $select = $connection->select()
            ->from($installer->getTable('store_website'))
            ->where('website_id = ?', 0);

        if ($connection->fetchOne($select) === false) {
            /**
             * Insert websites
             */
            $connection->insertForce(
                $installer->getTable('store_website'),
                [
                    'website_id' => 0,
                    'code' => 'admin',
                    'name' => 'Admin',
                    'sort_order' => 0,
                    'default_group_id' => 0,
                    'is_default' => 0
                ]
            );
            $connection->insertForce(
                $installer->getTable('store_website'),
                [
                    'website_id' => 1,
                    'code' => 'base',
                    'name' => 'Main Website',
                    'sort_order' => 0,
                    'default_group_id' => 1,
                    'is_default' => 1
                ]
            );

            /**
             * Insert store groups
             */
            $connection->insertForce(
                $installer->getTable('store_group'),
                ['group_id' => 0, 'website_id' => 0, 'name' => 'Default', 'root_category_id' => 0, 'default_store_id' => 0]
            );
            $connection->insertForce(
                $installer->getTable('store_group'),
                [
                    'group_id' => 1,
                    'website_id' => 1,
                    'name' => 'Main Website Store',
                    'root_category_id' => $this->getDefaultCategory()->getId(),
                    'default_store_id' => 1
                ]
            );

            /**
             * Insert stores
             */
            $connection->insertForce(
                $installer->getTable('store'),
                [
                    'store_id' => 0,
                    'code' => 'admin',
                    'website_id' => 0,
                    'group_id' => 0,
                    'name' => 'Admin',
                    'sort_order' => 0,
                    'is_active' => 1
                ]
            );
            $connection->insertForce(
                $installer->getTable('store'),
                [
                    'store_id' => 1,
                    'code' => 'default',
                    'website_id' => 1,
                    'group_id' => 1,
                    'name' => 'Default Store View',
                    'sort_order' => 0,
                    'is_active' => 1
                ]
            );
            $setup->endSetup();
        }
    }
}
