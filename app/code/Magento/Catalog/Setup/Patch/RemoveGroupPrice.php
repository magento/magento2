<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\VersionedDataPatch;

/**
 * Class RemoveGroupPrice
 * @package Magento\Catalog\Setup\Patch
 */
class RemoveGroupPrice implements DataPatchInterface, VersionedDataPatch
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from(
                $this->resourceConnection->getConnection()->getTableName('catalog_product_entity_group_price'),
                [
                    'entity_id',
                    'all_groups',
                    'customer_group_id',
                    new \Zend_Db_Expr('1'),
                    'value',
                    'website_id'
                ]
            );
        $select = $this->resourceConnection->getConnection()->insertFromSelect(
            $select,
            $this->resourceConnection->getConnection()->getTableName('catalog_product_entity_tier_price'),
            [
                'entity_id',
                'all_groups',
                'customer_group_id',
                'qty',
                'value',
                'website_id'
            ]
        );
        $this->resourceConnection->getConnection()->query($select);

        $categorySetupManager = $this->categorySetupFactory->create(
            ['resourceConnection' => $this->resourceConnection]
        );
        $categorySetupManager->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'group_price');
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
    public function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
