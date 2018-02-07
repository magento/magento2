<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Indexer;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\InventoryIndexer\Indexer\Source\SourceIndexer;
use Magento\InventoryApi\Api\GetSalableProductQtyInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceIndexerTest extends TestCase
{
    /**
     * @var SourceIndexer
     */
    private $sourceIndexer;

    /**
     * @var GetSalableProductQtyInterface
     */
    private $getSalableProductQty;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    protected function setUp()
    {
        $this->sourceIndexer = Bootstrap::getObjectManager()->get(SourceIndexer::class);

        $this->getSalableProductQty = Bootstrap::getObjectManager()
            ->get(GetSalableProductQtyInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
    }

    /**
     * We broke transaction during indexation so we need to clean db state manually
     */
    protected function tearDown()
    {
        $this->removeIndexData->execute([10, 20, 30]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testReindexRow()
    {
        $this->sourceIndexer->executeRow('eu-1');

        self::assertEquals(8.5, $this->getSalableProductQty->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getSalableProductQty->execute('SKU-1', 30));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testReindexList()
    {
        $this->sourceIndexer->executeList(['eu-1', 'us-1']);

        self::assertEquals(8.5, $this->getSalableProductQty->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getSalableProductQty->execute('SKU-1', 30));

        self::assertEquals(5, $this->getSalableProductQty->execute('SKU-2', 20));
        self::assertEquals(5, $this->getSalableProductQty->execute('SKU-2', 30));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testReindexAll()
    {
        $this->sourceIndexer->executeFull();

        self::assertEquals(8.5, $this->getSalableProductQty->execute('SKU-1', 10));
        self::assertEquals(8.5, $this->getSalableProductQty->execute('SKU-1', 30));

        self::assertEquals(5, $this->getSalableProductQty->execute('SKU-2', 20));
        self::assertEquals(5, $this->getSalableProductQty->execute('SKU-2', 30));
    }
}
