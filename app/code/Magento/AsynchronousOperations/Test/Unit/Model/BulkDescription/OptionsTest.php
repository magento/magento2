<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model\BulkDescription;

use Magento\AsynchronousOperations\Model\BulkDescription\Options;
use Magento\AsynchronousOperations\Model\BulkSummary;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /**
     * @var Options
     */
    private $model;

    /**
     * @var MockObject
     */
    private $bulkCollectionFactoryMock;

    /**
     * @var MockObject
     */
    private $userContextMock;

    protected function setUp(): void
    {
        $this->bulkCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->model = new Options(
            $this->bulkCollectionFactoryMock,
            $this->userContextMock
        );
    }

    public function testToOptionsArray()
    {
        $userId = 100;
        $collectionMock = $this->createMock(Collection::class);
        $selectMock = $this->createMock(Select::class);
        $this->bulkCollectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($userId);

        $collectionMock->expects($this->once())->method('getMainTable')->willReturn('table');

        $selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $selectMock->expects($this->once())->method('distinct')->with(true)->willReturnSelf();
        $selectMock->expects($this->once())->method('from')->with('table', ['description'])->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('user_id = ?', $userId)->willReturnSelf();

        $itemMock = $this->createPartialMock(
            BulkSummary::class,
            ['getDescription']
        );
        $itemMock->expects($this->exactly(2))->method('getDescription')->willReturn('description');

        $collectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);

        $expectedResult = [
            [
                'value' => 'description',
                'label' => 'description'
            ]
        ];

        $this->assertEquals($expectedResult, $this->model->toOptionArray());
    }
}
