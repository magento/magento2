<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventorySales\Setup\Operation\CreateSalesChannelTable;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateSalesChannelTable
     */
    private $createSalesChannelTable;

    /**
     * @param CreateSalesChannelTable $createSalesChannelTable
     */
    public function __construct(
        CreateSalesChannelTable $createSalesChannelTable
    ) {
        $this->createSalesChannelTable = $createSalesChannelTable;
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createSalesChannelTable->execute($setup);
        $setup->endSetup();
    }
}
