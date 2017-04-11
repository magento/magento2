<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Store\Group as StoreGroupIndexerPlugin;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Group as StoreGroup;
use Magento\Store\Model\ResourceModel\Group as StoreGroupResourceModel;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreGroupIndexerPlugin
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
     * @var StoreGroupResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var StoreGroup|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeGroupMock;

    protected function setUp()
    {
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(StoreGroupResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeGroupMock = $this->getMockBuilder(StoreGroup::class)
            ->disableOriginalConstructor()
            ->setMethods(['dataHasChangedFor', 'isObjectNew'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            StoreGroupIndexerPlugin::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    /**
     * @param bool $isObjectNew
     * @param bool $websiteChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider beforeAfterSaveDataProvider
     */
    public function testBeforeAfterSave($isObjectNew, $websiteChanged, $invalidateCounter)
    {
        $this->prepareIndexer($invalidateCounter);
        $this->storeGroupMock->expects(static::any())
            ->method('dataHasChangedFor')
            ->with('website_id')
            ->willReturn($websiteChanged);
        $this->storeGroupMock->expects(static::once())
            ->method('isObjectNew')
            ->willReturn($isObjectNew);
        $this->indexerMock->expects(static::exactly($invalidateCounter))
            ->method('invalidate');

        $this->plugin->beforeSave($this->subjectMock, $this->storeGroupMock);
        $this->assertSame($this->subjectMock, $this->plugin->afterSave($this->subjectMock, $this->subjectMock));
    }

    /**
     * @return array
     */
    public function beforeAfterSaveDataProvider()
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 0],
            [true, true, 0]
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
