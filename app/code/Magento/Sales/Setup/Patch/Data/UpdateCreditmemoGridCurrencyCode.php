<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Update credit memo grid currency code.
 */
class UpdateCreditmemoGridCurrencyCode implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);
        /** @var Mysql $connection */
        $connection = $salesSetup->getConnection();
        $creditMemoGridTable = $salesSetup->getTable('sales_creditmemo_grid');
        $orderTable = $salesSetup->getTable('sales_order');
        $select = $connection->select();
        $condition = 'so.entity_id = scg.order_id';
        $select->join(['so' => $orderTable], $condition, ['order_currency_code', 'base_currency_code']);
        $sql = $connection->updateFromSelect($select, ['scg' => $creditMemoGridTable]);
        $connection->query($sql);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.13';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
