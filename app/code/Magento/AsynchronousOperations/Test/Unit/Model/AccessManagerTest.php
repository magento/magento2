<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Model\AccessManager;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

class AccessManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AccessManager
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $userContextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $entityManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $bulkSummaryFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationMock;

    protected function setUp()
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->bulkSummaryFactoryMock = $this->createPartialMock(
            BulkSummaryInterfaceFactory::class,
            ['create']
        );
        $this->authorizationMock = $this->createMock(AuthorizationInterface::class);

        $this->model = new AccessManager(
            $this->userContextMock,
            $this->entityManagerMock,
            $this->bulkSummaryFactoryMock,
            $this->authorizationMock
        );
    }

    /**
     * @dataProvider summaryDataProvider
     * @param string $bulkUserId
     * @param bool $expectedResult
     */
    public function testIsAllowedForBulkUuid($bulkUserId, $expectedResult)
    {
        $adminId = 1;
        $uuid = 'test-001';
        $bulkSummaryMock = $this->createMock(BulkSummaryInterface::class);

        $this->bulkSummaryFactoryMock->expects($this->once())->method('create')->willReturn($bulkSummaryMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($bulkSummaryMock, $uuid)
            ->willReturn($bulkSummaryMock);

        $bulkSummaryMock->expects($this->once())->method('getUserId')->willReturn($bulkUserId);
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($adminId);

        $this->assertEquals($this->model->isAllowedForBulkUuid($uuid), $expectedResult);
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
