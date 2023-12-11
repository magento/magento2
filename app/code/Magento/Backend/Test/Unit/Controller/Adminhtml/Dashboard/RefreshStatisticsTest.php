<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Report\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics
 */
class RefreshStatisticsTest extends TestCase
{
    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var  RedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var Manager|MockObject
     */
    protected $messageManager;

    /**
     * @var Order|MockObject
     */
    protected $order;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var RefreshStatistics
     */
    protected $refreshStatisticsController;

    /**
     * @var Context|MockObject
     */
    protected $context;

    protected function setUp(): void
    {
        $reportTypes = [
            'sales' => Order::class
        ];

        $objectManagerHelper = new ObjectManager($this);

        $this->resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->resultRedirect = $this->createMock(Redirect::class);

        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();

        $this->messageManager = $this->createMock(Manager::class);

        $this->order = $this->createMock(Order::class);

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->refreshStatisticsController = $objectManagerHelper->getObject(
            RefreshStatistics::class,
            [
                'context' => $this->context,
                'reportTypes' => $reportTypes
            ]
        );
    }

    public function testExecute()
    {
        $path = '*/*';

        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('We updated lifetime statistic.'));

        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(Order::class)
            ->willReturn($this->order);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->refreshStatisticsController->execute()
        );
    }
}
