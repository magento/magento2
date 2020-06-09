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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
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
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

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

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            StoreViewIndexerPlugin::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    /**
     * @param bool $isObjectNew
     * @param int $invalidateCounter
     *
     * @dataProvider beforeAfterSaveDataProvider
     */
    public function testBeforeAfterSave($isObjectNew, $invalidateCounter)
    {
        $this->prepareIndexer($invalidateCounter);
        $this->storeMock->expects(static::once())
            ->method('isObjectNew')
            ->willReturn($isObjectNew);
        $this->indexerMock->expects(static::exactly($invalidateCounter))
            ->method('invalidate');

        $this->plugin->beforeSave($this->subjectMock, $this->storeMock);
        $this->assertSame($this->subjectMock, $this->plugin->afterSave($this->subjectMock, $this->subjectMock));
    }

    /**
     * @return array
     */
    public function beforeAfterSaveDataProvider()
    {
        return [
            [false, 0],
            [true, 1]
        ];
    }

    public function testAfterDelete()
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
    private function prepareIndexer($invalidateCounter)
    {
        $this->indexerRegistryMock->expects(static::exactly($invalidateCounter))
            ->method('get')
            ->with(FulltextIndexer::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
