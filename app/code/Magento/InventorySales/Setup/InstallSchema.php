<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySales\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventorySales\Setup\Operation\CreateStockChannelTable;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateStockChannelTable
     */
    private $createStockChannelTable;

    /**
     * @param CreateStockChannelTable $createStockChannelTable
     */
    public function __construct(
        CreateStockChannelTable $createStockChannelTable
    ) {
        $this->createStockChannelTable = $createStockChannelTable;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createStockChannelTable->execute($setup);

        $setup->endSetup();
    }
}
