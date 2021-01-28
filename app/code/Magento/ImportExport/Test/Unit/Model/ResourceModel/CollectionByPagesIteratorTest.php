<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\ResourceModel;

use \Magento\Framework\Data\Collection\AbstractDb;

/**
 * Test class for \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
 */
class CollectionByPagesIteratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
     */
    protected $_resourceModel;

    protected function setUp(): void
    {
        $this->_resourceModel = new \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator();
    }

    protected function tearDown(): void
    {
        unset($this->_resourceModel);
    }

    /**
     * @covers \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator::iterate
     */
    public function testIterate()
    {
        $pageSize = 2;
        $pageCount = 3;

        /** @var $callbackMock \PHPUnit\Framework\MockObject\MockObject */
        $callbackMock = $this->createPartialMock(\stdClass::class, ['callback']);

        $fetchStrategy = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );

        $select = $this->createMock(\Magento\Framework\DB\Select::class);

        $entityFactory = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        /** @var $collectionMock AbstractDb|\PHPUnit\Framework\MockObject\MockObject */
        $collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->setConstructorArgs([$entityFactory, $logger, $fetchStrategy])
            ->setMethods(['clear', 'setPageSize', 'setCurPage', 'count', 'getLastPageNumber', 'getSelect'])
            ->getMockForAbstractClass();

        $collectionMock->expects($this->any())->method('getSelect')->willReturn($select);

        $collectionMock->expects($this->exactly($pageCount + 1))->method('clear')->willReturnSelf();

        $collectionMock->expects($this->exactly($pageCount))->method('setPageSize')->willReturnSelf();

        $collectionMock->expects($this->exactly($pageCount))->method('setCurPage')->willReturnSelf();

        $collectionMock->expects($this->exactly($pageCount))->method('count')->willReturn($pageSize);

        $collectionMock->expects(
            $this->exactly($pageCount)
        )->method(
            'getLastPageNumber'
        )->willReturn(
            $pageCount
        );

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            for ($rowNumber = 1; $rowNumber <= $pageSize; $rowNumber++) {
                $itemId = ($pageNumber - 1) * $pageSize + $rowNumber;
                $item = new \Magento\Framework\DataObject(['id' => $itemId]);
                $collectionMock->addItem($item);

                $callbackMock->expects($this->at($itemId - 1))->method('callback')->with($item);
            }
        }

        $this->_resourceModel->iterate($collectionMock, $pageSize, [[$callbackMock, 'callback']]);
    }
}
