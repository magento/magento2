<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Setup;

use Magento\Catalog\Model\ResourceModel\Product\Relation;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

/**
 * Class \Magento\GroupedProduct\Setup\UpgradeData
 *
 * @since 2.1.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Relation
     * @since 2.1.0
     */
    private $relationProcessor;

    /**
     * UpgradeData constructor
     * @param Relation $relationProcessor
     * @since 2.1.0
     */
    public function __construct(Relation $relationProcessor)
    {
        $this->relationProcessor = $relationProcessor;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
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
        }

        $setup->endSetup();
    }
}
