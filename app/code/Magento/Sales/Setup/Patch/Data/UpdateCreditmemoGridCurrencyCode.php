<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
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
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $connection = $salesSetup->getConnection();
        $creditMemoGridTable = $salesSetup->getTable('sales_creditmemo_grid');
        $orderTable = $salesSetup->getTable('sales_order');
        // phpcs:disable Magento2.SQL.RawQuery
        $sql = "UPDATE {$creditMemoGridTable} AS scg 
        JOIN {$orderTable} AS so ON so.entity_id = scg.order_id 
        SET scg.order_currency_code = so.order_currency_code, 
        scg.base_currency_code = so.base_currency_code;";

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
