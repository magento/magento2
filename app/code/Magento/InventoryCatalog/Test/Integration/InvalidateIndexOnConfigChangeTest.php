<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class InvalidateIndexOnConfigChangeTest extends TestCase
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var IndexerInterface
     */
    private $inventoryIndexer;

    protected function setUp()
    {
        $this->eventManager = Bootstrap::getObjectManager()->get(EventManagerInterface::class);
        $indexerRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
        $this->inventoryIndexer = $indexerRegistry->get(InventoryIndexer::INDEXER_ID);
        $this->inventoryIndexer->reindexAll();
    }

    public function testIndexInvalidAfterConfigChange()
    {
        $this->eventManager->dispatch('admin_system_config_changed_section_cataloginventory');

        self::assertTrue($this->inventoryIndexer->isInvalid());
    }
}
