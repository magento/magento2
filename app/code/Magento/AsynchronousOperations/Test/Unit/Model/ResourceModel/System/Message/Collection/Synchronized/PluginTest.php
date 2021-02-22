<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Model\ResourceModel\System\Message\Collection\Synchronized;

use Magento\AsynchronousOperations\Model\ResourceModel\System\Message\Collection\Synchronized\Plugin;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\AsynchronousOperations\Model\Operation\Details;
use Magento\Framework\AuthorizationInterface;
use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;

/**
 * Test for Plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $messagefactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $bulkStatusMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $bulkNotificationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $userContextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $operationsDetailsMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $statusMapper;

    /**
     * @var string
     */
    private $resourceName = 'Magento_Logging::system_magento_logging_bulk_operations';

    protected function setUp(): void
    {
        $this->messagefactoryMock = $this->createPartialMock(
            \Magento\AdminNotification\Model\System\MessageFactory::class,
            ['create']
        );
        $this->bulkStatusMock = $this->getMockForAbstractClass(BulkStatusInterface::class);

        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->operationsDetailsMock = $this->createMock(Details::class);
        $this->authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->messageMock = $this->createMock(\Magento\AdminNotification\Model\System\Message::class);
        $this->collectionMock = $this->createMock(Synchronized::class);
        $this->bulkNotificationMock = $this->createMock(BulkNotificationManagement::class);
        $this->statusMapper = $this->createMock(\Magento\AsynchronousOperations\Model\StatusMapper::class);
        $this->plugin = new Plugin(
            $this->messagefactoryMock,
            $this->bulkStatusMock,
            $this->bulkNotificationMock,
            $this->userContextMock,
            $this->operationsDetailsMock,
            $this->authorizationMock,
            $this->statusMapper
        );
    }

    public function testAfterToArrayIfNotAllowed()
    {
        $result = [];
        $this->authorizationMock
            ->expects($this->once())
            ->method('isAllowed')
            ->with($this->resourceName)
            ->willReturn(false);
        $this->assertEquals($result, $this->plugin->afterToArray($this->collectionMock, $result));
    }

    /**
     * @param array $operationDetails
     * @dataProvider afterToDataProvider
     */
    public function testAfterTo($operationDetails)
    {
        $methods = ['getBulkId', 'getDescription', 'getStatus', 'getStartTime'];
        $bulkMock = $this->createPartialMock(\Magento\AsynchronousOperations\Model\BulkSummary::class, $methods);
        $result = ['items' =>[], 'totalRecords' => 1];
        $userBulks = [$bulkMock];
        $userId = 1;
        $bulkUuid = 2;
        $bulkArray = [
            'status' => \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::NOT_STARTED
        ];
        $bulkMock->expects($this->once())->method('getBulkId')->willReturn($bulkUuid);
        $this->operationsDetailsMock
            ->expects($this->once())
            ->method('getDetails')
            ->with($bulkUuid)
            ->willReturn($operationDetails);
        $bulkMock->expects($this->once())->method('getDescription')->willReturn('Bulk Description');
        $this->messagefactoryMock->expects($this->once())->method('create')->willReturn($this->messageMock);
        $this->messageMock->expects($this->once())->method('toArray')->willReturn($bulkArray);
        $this->authorizationMock
            ->expects($this->once())
            ->method('isAllowed')
            ->with($this->resourceName)
            ->willReturn(true);
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->bulkNotificationMock
            ->expects($this->once())
            ->method('getAcknowledgedBulksByUser')
            ->with($userId)
            ->willReturn([]);
        $this->statusMapper->expects($this->once())->method('operationStatusToBulkSummaryStatus');
        $this->bulkStatusMock->expects($this->once())->method('getBulksByUser')->willReturn($userBulks);
        $result2 = $this->plugin->afterToArray($this->collectionMock, $result);
        $this->assertEquals(2, $result2['totalRecords']);
    }

    /**
     * @return array
     */
    public function afterToDataProvider()
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
