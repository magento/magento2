<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryReservations\Setup\Operation\CreateReservationTable;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateReservationTable
     */
    private $createReservationTable;

    /**
     * @param CreateReservationTable $createReservationTable
     */
    public function __construct(
        CreateReservationTable $createReservationTable
    ) {
        $this->createReservationTable = $createReservationTable;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createReservationTable->execute($setup);

        $setup->endSetup();
    }
}
