<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model;

class AccessValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\AccessValidator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkSummaryFactoryMock;

    protected function setUp()
    {
        $this->userContextMock = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->entityManagerMock = $this->createMock(\Magento\Framework\EntityManager\EntityManager::class);
        $this->bulkSummaryFactoryMock = $this->createPartialMock(
            \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory::class,
            ['create']
        );

        $this->model = new \Magento\AsynchronousOperations\Model\AccessValidator(
            $this->userContextMock,
            $this->entityManagerMock,
            $this->bulkSummaryFactoryMock
        );
    }

    /**
     * @dataProvider summaryDataProvider
     * @param string $bulkUserId
     * @param bool $expectedResult
     */
    public function testIsAllowed($bulkUserId, $expectedResult)
    {
        $adminId = 1;
        $uuid = 'test-001';
        $bulkSummaryMock = $this->createMock(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class);

        $this->bulkSummaryFactoryMock->expects($this->once())->method('create')->willReturn($bulkSummaryMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($bulkSummaryMock, $uuid)
            ->willReturn($bulkSummaryMock);

        $bulkSummaryMock->expects($this->once())->method('getUserId')->willReturn($bulkUserId);
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($adminId);

        $this->assertEquals($this->model->isAllowed($uuid), $expectedResult);
    }

    /**
     * @return array
     */
    public static function summaryDataProvider()
    {
        return [
            [2, false],
            [1, true]
        ];
    }
}
