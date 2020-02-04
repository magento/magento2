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
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateProductRelations
 * @package Magento\GroupedProduct\Setup\Patch
 */
class UpdateProductRelations implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Relation
     */
    private $relationProcessor;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Catalog\Model\ResourceModel\Product\Relation $relationProcessor
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->relationProcessor = $relationProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

            $connection = $this->moduleDataSetup->getConnection();
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

        $this->moduleDataSetup->getConnection()->endSetup();
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
