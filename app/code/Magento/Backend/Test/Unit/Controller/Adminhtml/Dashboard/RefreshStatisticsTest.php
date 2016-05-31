<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            'sales' => 'Magento\Sales\Model\ResourceModel\Report\Order'
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resultRedirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resultRedirect = $this->getMock('Magento\Backend\Model\View\Result\Redirect', [], [], '', false);

        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );

        $this->messageManager = $this->getMock('\Magento\Framework\Message\Manager', [], [], '', false);

        $this->order = $this->getMock('Magento\Sales\Model\ResourceModel\Report\Order', [], [], '', false);

        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);

        $this->context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->refreshStatisticsController = $objectManagerHelper->getObject(
            'Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics',
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
            ->with('Magento\Sales\Model\ResourceModel\Report\Order')
            ->willReturn($this->order);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();

        $this->assertInstanceOf(
            'Magento\Backend\Model\View\Result\Redirect',
            $this->refreshStatisticsController->execute()
        );
    }
}
