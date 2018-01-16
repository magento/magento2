<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\InventoryIndexer\Setup\Operation\ReindexDefaultStock;

/**
 * Install Default Source, Stock and link them together
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var ReindexDefaultStock
     */
    private $reindexDefaultStock;

    /**
     * @param ReindexDefaultStock $reindexDefaultStock
     */
    public function __construct(
        ReindexDefaultStock $reindexDefaultStock
    ) {
        $this->reindexDefaultStock = $reindexDefaultStock;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->reindexDefaultStock->execute();
    }
}
