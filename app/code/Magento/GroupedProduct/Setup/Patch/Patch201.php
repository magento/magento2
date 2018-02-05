<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Setup\Patch;

use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201
{


    /**
     * @param Relation $relationProcessor
     */
    private $relationProcessor;
    /**
     * @param Relation $relationProcessor
     */
    private $relationProcessor;

    /**
     * @param Relation $relationProcessor @param Relation $relationProcessor
     */
    public function __construct(Relation $relationProcessor
        , Relation $relationProcessor)
    {
        $this->relationProcessor = $relationProcessor;
        $this->relationProcessor = $relationProcessor;
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

        $connection = $setup->getConnection();
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

        $setup->endSetup();

    }

}
