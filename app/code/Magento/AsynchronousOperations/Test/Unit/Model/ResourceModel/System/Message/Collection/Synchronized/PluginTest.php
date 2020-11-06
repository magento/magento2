<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model\ResourceModel\System\Message\Collection\Synchronized;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\AdminNotification\Model\System\Message;
use Magento\AdminNotification\Model\System\MessageFactory;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\AsynchronousOperations\Model\BulkSummary;
use Magento\AsynchronousOperations\Model\Operation\Details;
use Magento\AsynchronousOperations\Model\ResourceModel\System\Message\Collection\Synchronized\Plugin;
use Magento\AsynchronousOperations\Model\StatusMapper;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\Framework\Bulk\GetBulksByUserAndTypeInterface;
use Magento\Framework\Encryption\Encryptor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var MockObject
     */
    private $messagefactoryMock;

    /**
     * @var MockObject
     */
    private $bulkStatusMock;

    /**
     * @var MockObject
     */
    private $bulkNotificationMock;

    /**
     * @var MockObject
     */
    private $userContextMock;

    /**
     * @var MockObject
     */
    private $operationsDetailsMock;

    /**
     * @var MockObject
     */
    private $authorizationMock;

    /**
     * @var MockObject
     */
    private $messageMock;

    /**
     * @var MockObject
     */
    private $collectionMock;

    /**
     * @var MockObject
     */
    private $statusMapper;

    /**
     * @var GetBulksByUserAndTypeInterface|MockObject
     */
    private $getBulksByUserAndTypeMock;

    /**
     * @var Encryptor|MockObject
     */
    private $encryptor;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->messagefactoryMock = $this->createPartialMock(
            MessageFactory::class,
            ['create']
        );
        $this->bulkStatusMock = $this->getMockForAbstractClass(BulkStatusInterface::class);

        $this->bulkNotificationMock = $this->createMock(BulkNotificationManagement::class);
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->operationsDetailsMock = $this->createMock(Details::class);
        $this->authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->collectionMock = $this->createMock(Synchronized::class);
        $this->statusMapper = $this->createMock(StatusMapper::class);
        $this->encryptor = $this->createMock(Encryptor::class);
        $this->getBulksByUserAndTypeMock = $this->createMock(GetBulksByUserAndTypeInterface::class);

        $this->plugin = new Plugin(
            $this->messagefactoryMock,
            $this->bulkStatusMock,
            $this->bulkNotificationMock,
            $this->userContextMock,
            $this->operationsDetailsMock,
            $this->authorizationMock,
            $this->statusMapper,
            $this->encryptor,
            $this->getBulksByUserAndTypeMock,
        );
    }

    /**
     * After toArray when not allowed
     *
     * @return void
     */
    public function testAfterToArrayIfNotAllowed(): void
    {
        $result = [];
        $this->authorizationMock
            ->expects($this->once())
            ->method('isAllowed')
            ->willReturn(false);
        $this->assertEquals($result, $this->plugin->afterToArray($this->collectionMock, $result));
    }

    /**
     * @param array $operationDetails
     * @dataProvider afterToDataProvider
     * @return void
     */
    public function testAfterTo(array $operationDetails): void
    {
        $bulkMock = $this->getMockBuilder(BulkSummary::class)
            ->addMethods(['getStatus'])
            ->onlyMethods(['getBulkId', 'getDescription', 'getStartTime'])
            ->disableOriginalConstructor()
            ->getMock();
        $result = ['items' =>[], 'totalRecords' => 1];
        $userBulks = [$bulkMock];
        $userId = 1;
        $bulkUuid = 2;
        $bulkArray = [
            'status' => BulkSummaryInterface::NOT_STARTED
        ];
        $bulkMock->expects($this->once())->method('getBulkId')->willReturn($bulkUuid);
        $this->operationsDetailsMock
            ->expects($this->once())
            ->method('getDetails')
            ->with($bulkUuid)
            ->willReturn($operationDetails);
        $bulkMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('Bulk Description');
        $this->messagefactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->messageMock);
        $this->messageMock->expects($this->once())
            ->method('toArray')
            ->willReturn($bulkArray);
        $this->authorizationMock
            ->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);
        $this->bulkNotificationMock
            ->expects($this->once())
            ->method('getAcknowledgedBulksByUser')
            ->with($userId)
            ->willReturn([]);
        $this->getBulksByUserAndTypeMock->expects($this->once())
            ->method('execute')
            ->willReturn($userBulks);
        $result2 = $this->plugin->afterToArray($this->collectionMock, $result);

        $this->assertEquals(2, $result2['totalRecords']);
    }

    /**
     * @return array
     */
    public function afterToDataProvider(): array
    {
        return [
            [
                [
                    'operations_successful' => 0,
                    'operations_failed' => 0,
                    'operations_total' => 10
                ]
            ],
            [
                [
                    'operations_successful' => 1,
                    'operations_failed' => 2,
                    'operations_total' => 10
                ]
            ],
        ];
    }
}
