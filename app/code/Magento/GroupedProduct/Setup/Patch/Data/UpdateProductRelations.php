<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateProductRelations
 * @package Magento\GroupedProduct\Setup\Patch
 */
class UpdateProductRelations implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Relation
     */
    private $relationProcessor;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product\Relation $relationProcessor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->relationProcessor = $relationProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();

            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(
                    $this->relationProcessor->getTable('catalog_product_link'),
                    ['product_id', 'linked_product_id']
                )
                ->where('link_type_id = ?', Link::LINK_TYPE_GROUPED);

            $connection->query(
                $connection->insertFromSelect(
                    $select,
                    $this->relationProcessor->getMainTable(),
                    ['parent_id', 'child_id'],
                    AdapterInterface::INSERT_IGNORE
                )
            );

        $this->resourceConnection->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            InitializeGroupedProductLinks::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
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
