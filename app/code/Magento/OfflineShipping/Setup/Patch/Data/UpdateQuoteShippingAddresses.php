<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

class UpdateQuoteShippingAddresses implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        // setup default
        $this->resourceConnection->getConnection()->startSetup();
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $connection->getTableName('salesrule'),
            ['simple_free_shipping' => 0],
            [new \Zend_Db_Expr('simple_free_shipping IS NULL')]
        );
        $this->resourceConnection->getConnection()->endSetup();

        // setup sales
        $this->resourceConnection->getConnection('sales')->startSetup();
        $this->resourceConnection->getConnection('sales')->update(
            $this->resourceConnection->getConnection('sales')->getTableName('sales_order_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $this->resourceConnection->getConnection('sales')->endSetup();

        // setup checkout
        $this->resourceConnection->getConnection('checkout')->startSetup();
        $this->resourceConnection->getConnection('checkout')->update(
            $this->resourceConnection->getConnection('checkout')->getTableName('quote_address'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $this->resourceConnection->getConnection('checkout')->update(
            $this->resourceConnection->getConnection('checkout')->getTableName('quote_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $this->resourceConnection->getConnection('checkout')->endSetup();
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
