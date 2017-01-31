<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\ResourceModel;

use \Magento\Framework\Data\Collection\AbstractDb;

/**
 * Test class for \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
 */
class CollectionByPagesIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
     */
    protected $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = new \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator();
    }

    protected function tearDown()
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

        /** @var $callbackMock \PHPUnit_Framework_MockObject_MockObject */
        $callbackMock = $this->getMock('stdClass', ['callback']);

        $fetchStrategy = $this->getMockForAbstractClass('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');

        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);

        $entityFactory = $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false);
        $logger = $this->getMock('Psr\Log\LoggerInterface');

        /** @var $collectionMock AbstractDb|\PHPUnit_Framework_MockObject_MockObject */
        $collectionMock = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->setConstructorArgs([$entityFactory, $logger, $fetchStrategy])
            ->setMethods(['clear', 'setPageSize', 'setCurPage', 'count', 'getLastPageNumber', 'getSelect'])
            ->getMockForAbstractClass();

        $collectionMock->expects($this->any())->method('getSelect')->will($this->returnValue($select));

        $collectionMock->expects($this->exactly($pageCount + 1))->method('clear')->will($this->returnSelf());

        $collectionMock->expects($this->exactly($pageCount))->method('setPageSize')->will($this->returnSelf());

        $collectionMock->expects($this->exactly($pageCount))->method('setCurPage')->will($this->returnSelf());

        $collectionMock->expects($this->exactly($pageCount))->method('count')->will($this->returnValue($pageSize));

        $collectionMock->expects(
            $this->exactly($pageCount)
        )->method(
            'getLastPageNumber'
        )->will(
            $this->returnValue($pageCount)
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
