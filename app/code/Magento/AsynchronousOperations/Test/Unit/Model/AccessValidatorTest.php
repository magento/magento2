<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Model\AccessValidator;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\EntityManager\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccessValidatorTest extends TestCase
{
    /**
     * @var AccessValidator
     */
    private $model;

    /**
     * @var MockObject
     */
    private $userContextMock;

    /**
     * @var MockObject
     */
    private $entityManagerMock;

    /**
     * @var MockObject
     */
    private $bulkSummaryFactoryMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->bulkSummaryFactoryMock = $this->createPartialMock(
            BulkSummaryInterfaceFactory::class,
            ['create']
        );

        $this->model = new AccessValidator(
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
        $bulkSummaryMock = $this->getMockForAbstractClass(BulkSummaryInterface::class);

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
