<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

/**
 * Test for \Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics
 */
class RefreshStatisticsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var  \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics
     */
    protected $refreshStatisticsController;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $reportTypes = [
            'sales' => \Magento\Sales\Model\ResourceModel\Report\Order::class
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resultRedirectFactory = $this->getMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultRedirect = $this->getMock(\Magento\Backend\Model\View\Result\Redirect::class, [], [], '', false);

        $this->request = $this->getMock(\Magento\Framework\App\RequestInterface::class, [], [], '', false);
        $this->response = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );

        $this->messageManager = $this->getMock(\Magento\Framework\Message\Manager::class, [], [], '', false);

        $this->order = $this->getMock(\Magento\Sales\Model\ResourceModel\Report\Order::class, [], [], '', false);

        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class, [], [], '', false);

        $this->context = $this->getMock(\Magento\Backend\App\Action\Context::class, [], [], '', false);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->refreshStatisticsController = $objectManagerHelper->getObject(
            \Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics::class,
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
            ->method('addSuccess')
            ->with(__('We updated lifetime statistic.'));

        $this->objectManager->expects($this->any())
            ->method('create')
            ->with(\Magento\Sales\Model\ResourceModel\Report\Order::class)
            ->willReturn($this->order);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Backend\Model\View\Result\Redirect::class,
            $this->refreshStatisticsController->execute()
        );
    }
}
