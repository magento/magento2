<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Setup\Patch\Schema;

use Magento\Catalog\Helper\DefaultCategory;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Create stores and websites. Actually stores and websites are part of schema as
 * other modules schema relies on store and website presence.
 * @package Magento\Store\Setup\Patch\Schema
 */
class InitializeStoresAndWebsites implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @var DefaultCategory
     */
    private $defaultCategory;

    /**
     * @var \Magento\Catalog\Helper\DefaultCategoryFactory
     */
    private $defaultCategoryFactory;

    /**
     * PatchInitial constructor.
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        \Magento\Catalog\Helper\DefaultCategoryFactory $defaultCategoryFactory
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->defaultCategoryFactory = $defaultCategoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection();
        $select = $connection->select()
            ->from($this->schemaSetup->getTable('store_website'))
            ->where('website_id = ?', 0);

        if ($connection->fetchOne($select) === false) {
            /**
             * Insert websites
             */
            $connection->insertForce(
                $this->schemaSetup->getTable('store_website'),
                [
                    'website_id' => 0,
                    'code' => WebsiteInterface::ADMIN_CODE,
                    'name' => 'Admin',
                    'sort_order' => 0,
                    'default_group_id' => 0,
                    'is_default' => 0
                ]
            );
            $connection->insertForce(
                $this->schemaSetup->getTable('store_website'),
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
                $this->schemaSetup->getTable('store_group'),
                [
                    'group_id' => 0,
                    'website_id' => 0,
                    'name' => 'Default',
                    'root_category_id' => 0,
                    'default_store_id' => 0
                ]
            );
            $connection->insertForce(
                $this->schemaSetup->getTable('store_group'),
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
                $this->schemaSetup->getTable('store'),
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
                $this->schemaSetup->getTable('store'),
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
            $this->schemaSetup->endSetup();
        }
    }

    /**
     * Get default category.
     *
     * @deprecated 100.1.0
     * @return DefaultCategory
     */
    private function getDefaultCategory()
    {
        if ($this->defaultCategory === null) {
            $this->defaultCategory = $this->defaultCategoryFactory->create();
        }
        return $this->defaultCategory;
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
