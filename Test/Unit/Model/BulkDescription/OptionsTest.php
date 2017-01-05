<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model\BulkDescription;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\BulkDescription\Options
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    protected function setUp()
    {
        $this->bulkCollectionFactoryMock = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->userContextMock = $this->getMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->model = new \Magento\AsynchronousOperations\Model\BulkDescription\Options(
            $this->bulkCollectionFactoryMock,
            $this->userContextMock
        );
    }

    public function testToOptionsArray()
    {
        $userId = 100;
        $collectionMock = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection::class,
            [],
            [],
            '',
            false
        );
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $this->bulkCollectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($userId);

        $collectionMock->expects($this->once())->method('getMainTable')->willReturn('table');

        $selectMock->expects($this->once())->method('reset')->willReturnSelf();
        $selectMock->expects($this->once())->method('distinct')->with(true)->willReturnSelf();
        $selectMock->expects($this->once())->method('from')->with('table', ['description'])->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('user_id = ?', $userId)->willReturnSelf();

        $itemMock = $this->getMock(
            \Magento\AsynchronousOperations\Model\BulkSummary::class,
            ['getDescription'],
            [],
            '',
            false
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
