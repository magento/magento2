<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Order;

class HistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\History
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderCollectionFactoryInterface;

    /**
     * @var \Magento\Framework\App\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageTitleMock;

    protected function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->orderCollectionFactory =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->orderCollectionFactoryInterface =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface::class)
                ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturn($this->orderCollectionFactoryInterface);
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
            ->willReturn($customerId);

        $statuses = ['pending', 'processing', 'comlete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn($statuses);

        $orderCollection = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Collection::class,
            ['addFieldToSelect', 'addFieldToFilter', 'setOrder']
        );

        $this->context->expects($this->any())
            ->method('getPageConfig')
            ->willReturn($this->pageConfig);

        $orderCollection->expects($this->at(0))
            ->method('addFieldToSelect')
            ->with($this->equalTo('*'))
            ->willReturnSelf();
        $orderCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('status', $this->equalTo(['in' => $statuses]))
            ->willReturnSelf();
        $orderCollection->expects($this->at(2))
            ->method('setOrder')
            ->with('created_at', 'desc')
            ->willReturnSelf();
        $this->orderCollectionFactoryInterface->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($orderCollection);
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
