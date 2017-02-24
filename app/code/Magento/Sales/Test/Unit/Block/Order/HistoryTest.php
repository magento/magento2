<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Order;

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
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCollectionFactoryInterface;

    /**
     * @var \Magento\Framework\App\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

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

    protected function setUp()
    {
        $this->context = $this->getMock(
            \Magento\Framework\View\Element\Template\Context::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->orderCollectionFactory =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->orderCollectionFactoryInterface =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface::class)
                ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class, [], [], '', false);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->orderCollectionFactoryInterface));
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManager);

        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->setMethods(['getCustomerId'])->disableOriginalConstructor()->getMock();

        $this->orderConfig = $this->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->setMethods(['getVisibleOnFrontStatuses'])->disableOriginalConstructor()->getMock();

        $this->pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
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
            \Magento\Sales\Model\ResourceModel\Order\Collection::class,
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
            ->with('status', $this->equalTo(['in' => $statuses]))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(2))
            ->method('setOrder')
            ->with('created_at', 'desc')
            ->will($this->returnSelf());
        $this->orderCollectionFactoryInterface->expects($this->atLeastOnce())
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
