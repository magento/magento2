<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\ResourceModel;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator
 */
class CollectionByPagesIteratorTest extends TestCase
{
    /**
     * @var CollectionByPagesIterator
     */
    protected $_resourceModel;

    protected function setUp(): void
    {
        $this->_resourceModel = new CollectionByPagesIterator();
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

        /** @var $callbackMock \PHPUnit_Framework_MockObject_MockObject */
        $callbackMock = $this->createPartialMock(\stdClass::class, ['callback']);

        $fetchStrategy = $this->getMockForAbstractClass(
            FetchStrategyInterface::class
        );

        $select = $this->createMock(Select::class);

        $entityFactory = $this->createMock(EntityFactory::class);
        $logger = $this->createMock(LoggerInterface::class);

        /** @var $collectionMock AbstractDb|\PHPUnit_Framework_MockObject_MockObject */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
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
                $item = new DataObject(['id' => $itemId]);
                $collectionMock->addItem($item);

                $callbackMock->expects($this->at($itemId - 1))->method('callback')->with($item);
            }
        }

        $this->_resourceModel->iterate($collectionMock, $pageSize, [[$callbackMock, 'callback']]);
    }
}
