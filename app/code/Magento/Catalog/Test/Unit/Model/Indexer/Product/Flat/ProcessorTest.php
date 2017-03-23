<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat;

use Magento\Catalog\Model\Indexer\Product\Flat\Processor;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Processor
     */
    protected $_model;

    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexerMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stateMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_indexerMock = $this->getMock(
            \Magento\Indexer\Model\Indexer::class,
            ['getId', 'invalidate'],
            [],
            '',
            false
        );
        $this->_indexerMock->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->_stateMock = $this->getMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\State::class,
            ['isFlatEnabled'],
            [],
            '',
            false
        );
        $this->indexerRegistryMock = $this->getMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
        );
        $this->_model = $this->_objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::class,
            [
                'indexerRegistry' => $this->indexerRegistryMock,
                'state'  => $this->_stateMock
            ]
        );
    }

    /**
     * Test get indexer instance
     */
    public function testGetIndexer()
    {
        $this->prepareIndexer();
        $this->assertInstanceOf(\Magento\Indexer\Model\Indexer::class, $this->_model->getIndexer());
    }

    /**
     * Test mark indexer as invalid if enabled
     */
    public function testMarkIndexerAsInvalid()
    {
        $this->_stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue(true));
        $this->_indexerMock->expects($this->once())->method('invalidate');
        $this->prepareIndexer();
        $this->_model->markIndexerAsInvalid();
    }

    /**
     * Test mark indexer as invalid if disabled
     */
    public function testMarkDisabledIndexerAsInvalid()
    {
        $this->_stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue(false));
        $this->_indexerMock->expects($this->never())->method('invalidate');
        $this->_model->markIndexerAsInvalid();
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Processor::INDEXER_ID)
            ->will($this->returnValue($this->_indexerMock));
    }

    /**
     * @param bool $isFlatEnabled
     * @param bool $forceReindex
     * @param bool $isScheduled
     * @dataProvider dataProviderReindexRow
     */
    public function testReindexRow(
        $isFlatEnabled,
        $forceReindex,
        $isScheduled
    ) {
        $this->_stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn($isFlatEnabled);

        $indexerMock = $this->getMockBuilder(\Magento\Indexer\Model\Indexer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(Processor::INDEXER_ID)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->any())
            ->method('isScheduled')
            ->willReturn($isScheduled);
        $indexerMock->expects($this->never())
            ->method('reindexRow');

        $this->_model->reindexRow(1, $forceReindex);
    }

    public function dataProviderReindexRow()
    {
        return [
            [false, false, null],
            [true, false, true],
        ];
    }

    public function testReindexRowForce()
    {
        $id = 1;

        $this->_stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn(true);

        $indexerMock = $this->getMockBuilder(\Magento\Indexer\Model\Indexer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(Processor::INDEXER_ID)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->any())
            ->method('isScheduled')
            ->willReturn(true);
        $indexerMock->expects($this->any())
            ->method('reindexList')
            ->with($id)
            ->willReturnSelf();

        $this->_model->reindexRow($id, true);
    }

    /**
     * @param bool $isFlatEnabled
     * @param bool $forceReindex
     * @param bool $isScheduled
     * @dataProvider dataProviderReindexList
     */
    public function testReindexList(
        $isFlatEnabled,
        $forceReindex,
        $isScheduled
    ) {
        $this->_stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn($isFlatEnabled);

        $indexerMock = $this->getMockBuilder(\Magento\Indexer\Model\Indexer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(Processor::INDEXER_ID)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->any())
            ->method('isScheduled')
            ->willReturn($isScheduled);
        $indexerMock->expects($this->never())
            ->method('reindexList');

        $this->_model->reindexList([1], $forceReindex);
    }

    public function dataProviderReindexList()
    {
        return [
            [false, false, null],
            [true, false, true],
        ];
    }

    public function testReindexListForce()
    {
        $ids = [1];

        $this->_stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn(true);

        $indexerMock = $this->getMockBuilder(\Magento\Indexer\Model\Indexer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(Processor::INDEXER_ID)
            ->willReturn($indexerMock);

        $indexerMock->expects($this->any())
            ->method('isScheduled')
            ->willReturn(true);
        $indexerMock->expects($this->any())
            ->method('reindexList')
            ->with($ids)
            ->willReturnSelf();

        $this->_model->reindexList($ids, true);
    }
}
