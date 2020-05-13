<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Controller\Adminhtml\Bulk;

use Magento\AsynchronousOperations\Controller\Adminhtml\Bulk\Retry;
use Magento\AsynchronousOperations\Model\BulkManagement;
use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RetryTest extends TestCase
{
    /**
     * @var Retry
     */
    private $model;

    /**
     * @var MockObject
     */
    private $bulkManagementMock;

    /**
     * @var MockObject
     */
    private $notificationManagementMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var MockObject
     */
    private $resultRedirectMock;

    /**
     * @var MockObject
     */
    private $resultFactoryMock;

    /**
     * @var MockObject
     */
    private $jsonResultMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->bulkManagementMock = $this->createMock(BulkManagement::class);
        $this->notificationManagementMock = $this->createMock(BulkNotificationManagement::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultFactoryMock = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->jsonResultMock = $this->createMock(Json::class);

        $this->resultRedirectFactoryMock = $this->createPartialMock(RedirectFactory::class, ['create']);
        $this->resultRedirectMock = $this->createMock(Redirect::class);

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

        $this->requestMock
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

        $this->requestMock
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
