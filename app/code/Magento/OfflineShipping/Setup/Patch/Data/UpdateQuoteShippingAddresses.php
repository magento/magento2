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
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateQuoteShippingAddresses implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        // setup default
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        $salesConnection = $this->moduleDataSetup->getConnection('sales');
        $checkoutConnection = $this->moduleDataSetup->getConnection('checkout');
        $connection->update(
            $this->moduleDataSetup->getTable('salesrule'),
            ['simple_free_shipping' => 0],
            [new \Zend_Db_Expr('simple_free_shipping IS NULL')]
        );
        $this->moduleDataSetup->getConnection()->endSetup();

        // setup sales
        $salesConnection->startSetup();
        $salesConnection->update(
            $this->moduleDataSetup->getTable('sales_order_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $salesConnection->endSetup();

        // setup checkout
        $checkoutConnection->startSetup();
        $checkoutConnection->update(
            $this->moduleDataSetup->getTable('quote_address'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $checkoutConnection->update(
            $this->moduleDataSetup->getTable('quote_item'),
            ['free_shipping' => 0],
            [new \Zend_Db_Expr('free_shipping IS NULL')]
        );
        $checkoutConnection->endSetup();
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
