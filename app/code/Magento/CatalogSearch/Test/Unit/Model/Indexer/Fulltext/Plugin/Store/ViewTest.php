<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store\View as StoreViewIndexerPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Store\Model\Store;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

class ViewTest extends \PHPUnit\Framework\TestCase
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
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerMock;

    /**
     * @var StoreResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    protected function setUp()
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
