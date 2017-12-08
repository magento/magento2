<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Inventory\Setup\Operation\CreateReservationTable;
use Magento\Inventory\Setup\Operation\CreateSourceCarrierLinkTable;
use Magento\Inventory\Setup\Operation\CreateSourceItemTable;
use Magento\Inventory\Setup\Operation\CreateSourceTable;
use Magento\Inventory\Setup\Operation\CreateStockSourceLinkTable;
use Magento\Inventory\Setup\Operation\CreateStockTable;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateSourceTable
     */
    private $createSourceTable;

    /**
     * @var CreateSourceCarrierLinkTable
     */
    private $createSourceCarrierLinkTable;

    /**
     * @var CreateSourceItemTable
     */
    private $createSourceItemTable;

    /**
     * @var CreateStockTable
     */
    private $createStockTable;

    /**
     * @var CreateStockSourceLinkTable
     */
    private $createStockSourceLinkTable;

    /**
     * @var CreateReservationTable
     */
    private $createReservationTable;

    /**
     * @param CreateSourceTable $createSourceTable
     * @param CreateSourceCarrierLinkTable $createSourceCarrierLinkTable
     * @param CreateSourceItemTable $createSourceItemTable
     * @param CreateStockTable $createStockTable
     * @param CreateStockSourceLinkTable $createStockSourceLinkTable
     * @param CreateReservationTable $createReservationTable
     */
    public function __construct(
        CreateSourceTable $createSourceTable,
        CreateSourceCarrierLinkTable $createSourceCarrierLinkTable,
        CreateSourceItemTable $createSourceItemTable,
        CreateStockTable $createStockTable,
        CreateStockSourceLinkTable $createStockSourceLinkTable,
        CreateReservationTable $createReservationTable
    ) {
        $this->createSourceTable = $createSourceTable;
        $this->createSourceCarrierLinkTable = $createSourceCarrierLinkTable;
        $this->createSourceItemTable = $createSourceItemTable;
        $this->createStockTable = $createStockTable;
        $this->createStockSourceLinkTable = $createStockSourceLinkTable;
        $this->createReservationTable = $createReservationTable;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createSourceTable->execute($setup);
        $this->createSourceCarrierLinkTable->execute($setup);
        $this->createSourceItemTable->execute($setup);
        $this->createStockTable->execute($setup);
        $this->createStockSourceLinkTable->execute($setup);
        $this->createReservationTable->execute($setup);

        $setup->endSetup();
    }
}
