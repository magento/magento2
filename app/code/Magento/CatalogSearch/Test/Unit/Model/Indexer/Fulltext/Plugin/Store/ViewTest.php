<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store\View as StoreViewIndexerPlugin;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var StoreViewIndexerPlugin
     */
    private $plugin;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var IndexerInterface|MockObject
     */
    private $indexerMock;

    /**
     * @var StoreResourceModel|MockObject
     */
    private $subjectMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(StoreResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['isObjectNew'])
            ->getMock();

        $this->plugin = new StoreViewIndexerPlugin($this->indexerRegistryMock);
    }

    /**
     * @param bool $isObjectNew
     * @param int $invalidateCounter
     *
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave(bool $isObjectNew, int $invalidateCounter): void
    {
        $this->prepareIndexer($invalidateCounter);
        $this->storeMock->expects(static::once())
            ->method('isObjectNew')
            ->willReturn($isObjectNew);
        $this->indexerMock->expects(static::exactly($invalidateCounter))
            ->method('invalidate');

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterSave($this->subjectMock, $this->subjectMock, $this->storeMock)
        );
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider(): array
    {
        return [
            [false, 0],
            [true, 1]
        ];
    }

    public function testAfterDelete(): void
    {
        $this->prepareIndexer(1);
        $this->indexerMock->expects(static::once())
            ->method('invalidate');

        $this->assertSame($this->subjectMock, $this->plugin->afterDelete($this->subjectMock, $this->subjectMock));
    }

    /**
     * Prepare expectations for indexer
     *
     * @param int $invalidateCounter
     * @return void
     */
    private function prepareIndexer(int $invalidateCounter): void
    {
        $this->indexerRegistryMock->expects(static::exactly($invalidateCounter))
            ->method('get')
            ->with(FulltextIndexer::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
