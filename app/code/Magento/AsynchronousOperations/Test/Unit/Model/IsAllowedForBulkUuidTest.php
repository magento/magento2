<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Model\GetGlobalAllowedUserTypes;
use Magento\AsynchronousOperations\Model\IsAllowedForBulkUuid;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\AsynchronousOperations\Model\IsAllowedForBulkUuid.
 */
class IsAllowedForBulkUuidTest extends TestCase
{
    /**
     * @var IsAllowedForBulkUuid
     */
    private $model;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var EntityManager|MockObject
     */
    private $entityManagerMock;

    /**
     * @var BulkSummaryInterfaceFactory|MockObject
     */
    private $bulkSummaryFactoryMock;

    /**
     * @var GetGlobalAllowedUserTypes|MockObject
     */
    private $getGlobalAllowedUserTypes;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->bulkSummaryFactoryMock = $this->createPartialMock(
            BulkSummaryInterfaceFactory::class,
            ['create']
        );
        $this->getGlobalAllowedUserTypes = $this->createMock(GetGlobalAllowedUserTypes::class);

        $this->model = new IsAllowedForBulkUuid(
            $this->userContextMock,
            $this->entityManagerMock,
            $this->bulkSummaryFactoryMock,
            $this->getGlobalAllowedUserTypes
        );
    }

    /**
     * @dataProvider summaryDataProvider
     * @param int $bulkUserId
     * @param bool $expectedResult
     * @return void
     */
    public function testIsAllowedForBulkUuid(int $bulkUserId, bool $expectedResult): void
    {
        $adminId = 1;
        $uuid = 'test-001';
        $bulkSummaryMock = $this->createMock(BulkSummaryInterface::class);

        $this->bulkSummaryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bulkSummaryMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($bulkSummaryMock, $uuid)
            ->willReturn($bulkSummaryMock);

        $bulkSummaryMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($bulkUserId);
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($adminId);

        $this->assertEquals($this->model->execute($uuid), $expectedResult);
    }

    /**
     * @return array
     */
    public static function summaryDataProvider(): array
    {
        return [
            [2, false],
            [1, true]
        ];
    }
}
