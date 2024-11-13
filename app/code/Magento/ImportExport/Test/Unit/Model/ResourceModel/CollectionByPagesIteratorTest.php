<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\ResourceModel;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator;
use PHPUnit\Framework\MockObject\MockObject;
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_resourceModel = new CollectionByPagesIterator();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unset($this->_resourceModel);
    }

    /**
     * @return void
     * @covers \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator::iterate
     */
    public function testIterate(): void
    {
        $pageSize = 2;
        $pageCount = 3;

        /** @var MockObject $callbackMock */
        $callbackMock = $this->getMockBuilder(\stdClass::class)->addMethods(['callback'])
            ->disableOriginalConstructor()
            ->getMock();

        $fetchStrategy = $this->getMockForAbstractClass(
            FetchStrategyInterface::class
        );

        $select = $this->createMock(Select::class);

        $entityFactory = $this->createMock(EntityFactory::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        /** @var AbstractDb|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)->setConstructorArgs(
            [
                $entityFactory,
                $logger,
                $fetchStrategy
            ]
        )
            ->onlyMethods(
                [
                    'clear',
                    'setPageSize',
                    'setCurPage',
                    'count',
                    'getLastPageNumber',
                    'getSelect'
                ]
            )
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
        $withArgs = [];

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            for ($rowNumber = 1; $rowNumber <= $pageSize; $rowNumber++) {
                $itemId = ($pageNumber - 1) * $pageSize + $rowNumber;
                $item = new DataObject(['id' => $itemId]);
                $collectionMock->addItem($item);
                $withArgs[] = [$item];
            }
        }
        $callbackMock
            ->method('callback')
            ->willReturnCallback(function () use ($withArgs) {
                static $callCount = 0;
                if ($callCount < count($withArgs)) {
                    $args = $withArgs[$callCount];
                    if ($args) {
                        $callCount++;
                        return null;
                    }
                }
            });

        $this->_resourceModel->iterate($collectionMock, $pageSize, [[$callbackMock, 'callback']]);
    }
}
