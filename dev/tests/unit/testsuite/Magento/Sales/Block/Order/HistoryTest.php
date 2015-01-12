<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order;

class HistoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\History
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    public function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false, false);
        $this->orderCollectionFactory = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\CollectionFactory')
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->setMethods(['getCustomerId'])->disableOriginalConstructor()->getMock();

        $this->orderConfig = $this->getMockBuilder('Magento\Sales\Model\Order\Config')
            ->setMethods(['getVisibleOnFrontStatuses'])->disableOriginalConstructor()->getMock();

        $this->pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()->getMock();
        $this->pageTitleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructMethod()
    {
        $data = [];

        $customerId = 25;
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $statuses = ['pending', 'processing', 'comlete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->will($this->returnValue($statuses));

        $orderCollection = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Collection',
            ['addFieldToSelect', 'addFieldToFilter', 'setOrder'],
            [],
            '',
            false,
            false
        );

        $this->context->expects($this->any())
            ->method('getPageConfig')
            ->willReturn($this->pageConfig);

        $orderCollection->expects($this->at(0))
            ->method('addFieldToSelect')
            ->with($this->equalTo('*'))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('customer_id', $this->equalTo($customerId))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('status', $this->equalTo(['in' => $statuses]))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(3))
            ->method('setOrder')
            ->with('created_at', 'desc')
            ->will($this->returnSelf());
        $this->orderCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($orderCollection));
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->atLeastOnce())
            ->method('set')
            ->willReturnSelf();

        $this->model = new \Magento\Sales\Block\Order\History(
            $this->context,
            $this->orderCollectionFactory,
            $this->customerSession,
            $this->orderConfig,
            $data
        );
        $this->assertEquals($orderCollection, $this->model->getOrders());
    }
}
