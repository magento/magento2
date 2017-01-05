<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Controller\Adminhtml\Notification;

use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\AsynchronousOperations\Controller\Adminhtml\Notification\Dismiss;
use Magento\Framework\Controller\Result\Json;

class DismissTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Dismiss
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonResultMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->notificationManagementMock = $this->getMock(BulkNotificationManagement::class, [], [], '', false);
        $this->requestMock = $this->getMock(RequestInterface::class);
        $this->resultFactoryMock = $this->getMock(
            ResultFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->jsonResultMock = $this->getMock(Json::class, [], [], '', false);

        $this->model = $objectManager->getObject(
            Dismiss::class,
            [
                'notificationManagement' => $this->notificationManagementMock,
                'request' => $this->requestMock,
                'resultFactory' => $this->resultFactoryMock,
            ]
        );
    }

    public function testExecute()
    {
        $bulkUuids = ['49da7406-1ec3-4100-95ae-9654c83a6801'];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('uuid', [])
            ->willReturn($bulkUuids);

        $this->notificationManagementMock->expects($this->once())
            ->method('acknowledgeBulks')
            ->with($bulkUuids)
            ->willReturn(true);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON, [])
            ->willReturn($this->jsonResultMock);

        $this->assertEquals($this->jsonResultMock, $this->model->execute());
    }

    public function testExecuteSetsBadRequestResponseStatusIfBulkWasNotAcknowledgedCorrectly()
    {
        $bulkUuids = ['49da7406-1ec3-4100-95ae-9654c83a6801'];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('uuid', [])
            ->willReturn($bulkUuids);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON, [])
            ->willReturn($this->jsonResultMock);

        $this->notificationManagementMock->expects($this->once())
            ->method('acknowledgeBulks')
            ->with($bulkUuids)
            ->willReturn(false);

        $this->jsonResultMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(400);

        $this->assertEquals($this->jsonResultMock, $this->model->execute());
    }
}
