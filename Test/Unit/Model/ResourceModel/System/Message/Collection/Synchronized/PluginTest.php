<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * Class PluginTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $messagefactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkStatusMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkNotificationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationsDetailsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $statusMapper;

    /**
     * @var string
     */
    private $resourceName = 'Magento_Logging::system_magento_logging_bulk_operations';

    protected function setUp()
    {
        $this->messagefactoryMock = $this->getMock(
            \Magento\AdminNotification\Model\System\MessageFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->bulkStatusMock = $this->getMock(BulkStatusInterface::class);

        $this->userContextMock = $this->getMock(UserContextInterface::class);
        $this->operationsDetailsMock = $this->getMock(Details::class, [], [], '', false);
        $this->authorizationMock = $this->getMock(AuthorizationInterface::class);
        $this->messageMock = $this->getMock(\Magento\AdminNotification\Model\System\Message::class, [], [], '', false);
        $this->collectionMock = $this->getMock(Synchronized::class, [], [], '', false);
        $this->bulkNotificationMock = $this->getMock(BulkNotificationManagement::class, [], [], '', false);
        $this->statusMapper = $this->getMock(
            \Magento\AsynchronousOperations\Model\StatusMapper::class,
            [],
            [],
            '',
            false
        );
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
        $bulkMock = $this->getMock(\Magento\AsynchronousOperations\Model\BulkSummary::class, $methods, [], '', false);
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

    public function afterToDataProvider()
    {
        return [
            ['operations_successful' => 0,
                'operations_failed' => 0,
                'operations_total' => 10
            ],
            ['operations_successful' => 1,
                'operations_failed' => 2,
                'operations_total' => 10
            ],
        ];
    }
}
