<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Controller\Adminhtml\Bulk;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\AsynchronousOperations\Controller\Adminhtml\Bulk\Retry;
use Magento\AsynchronousOperations\Model\BulkManagement;
use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;

class RetryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Retry
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkManagementMock;

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
    private $resultRedirectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

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
        $this->bulkManagementMock = $this->getMock(BulkManagement::class, [], [], '', false);
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

        $this->resultRedirectFactoryMock = $this->getMock(
            RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultRedirectMock = $this->getMock(Redirect::class, [], [], '', false);

        $this->model = $objectManager->getObject(
            Retry::class,
            [
                'bulkManagement' => $this->bulkManagementMock,
                'notificationManagement' => $this->notificationManagementMock,
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'resultFactory' => $this->resultFactoryMock,
            ]
        );
    }

    public function testExecute()
    {
        $bulkUuid = '49da7406-1ec3-4100-95ae-9654c83a6801';
        $operationsToRetry = [
            [
                'key' => 'value',
                'error_code' => 1111,
            ],
            [
                'error_code' => 2222,
            ],
            [
                'error_code' => '3333',
            ],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['uuid', null, $bulkUuid],
                ['operations_to_retry', [], $operationsToRetry],
                ['isAjax', null, false],
            ]);

        $this->bulkManagementMock->expects($this->once())
            ->method('retryBulk')
            ->with($bulkUuid, [1111, 2222, 3333]);

        $this->notificationManagementMock->expects($this->once())
            ->method('ignoreBulks')
            ->with([$bulkUuid])
            ->willReturn(true);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('bulk/index');

        $this->model->execute();
    }

    public function testExecuteReturnsJsonResultWhenRequestIsSentViaAjax()
    {
        $bulkUuid = '49da7406-1ec3-4100-95ae-9654c83a6801';
        $operationsToRetry = [
            [
                'key' => 'value',
                'error_code' => 1111,
            ],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['uuid', null, $bulkUuid],
                ['operations_to_retry', [], $operationsToRetry],
                ['isAjax', null, true],
            ]);

        $this->bulkManagementMock->expects($this->once())
            ->method('retryBulk')
            ->with($bulkUuid, [1111]);

        $this->notificationManagementMock->expects($this->once())
            ->method('ignoreBulks')
            ->with([$bulkUuid])
            ->willReturn(true);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON, [])
            ->willReturn($this->jsonResultMock);

        $this->jsonResultMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(200);

        $this->assertEquals($this->jsonResultMock, $this->model->execute());
    }
}
