<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryConfiguration\Setup\Operation\CreateSourceConfigurationTable;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @var CreateSourceConfigurationTable
     */
    protected $createSourceNotificationTable;

    /**
     * InstallSchema constructor.
     *
     * @param CreateSourceConfigurationTable $createSourceNotificationTable
     */
    public function __construct(
        CreateSourceConfigurationTable $createSourceNotificationTable
    ) {
        $this->createSourceNotificationTable = $createSourceNotificationTable;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createSourceNotificationTable->execute($setup);
        $setup->endSetup();
    }
}
