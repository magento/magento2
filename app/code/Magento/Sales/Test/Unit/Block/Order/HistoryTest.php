<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Title;
use Magento\Sales\Block\Order\History;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HistoryTest extends TestCase
{
    /**
     * @var History
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var CollectionFactoryInterface|MockObject
     */
    private $orderCollectionFactoryInterface;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManager;

    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    /**
     * @var Config|MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Framework\View\Page\Config|MockObject
     */
    protected $pageConfig;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->orderCollectionFactory =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])->getMock();
        $this->orderCollectionFactoryInterface =
            $this->getMockBuilder(CollectionFactoryInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])->getMockForAbstractClass();
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturn($this->orderCollectionFactoryInterface);
        ObjectManager::setInstance($this->objectManager);

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->setMethods(['getCustomerId'])->disableOriginalConstructor()
            ->getMock();

        $this->orderConfig = $this->getMockBuilder(Config::class)
            ->setMethods(['getVisibleOnFrontStatuses'])->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
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
            Collection::class,
            ['addFieldToSelect', 'addFieldToFilter', 'setOrder']
        );

        $this->context->expects($this->any())
            ->method('getPageConfig')
            ->willReturn($this->pageConfig);

        $orderCollection->expects($this->at(0))
            ->method('addFieldToSelect')
            ->with('*')->willReturnSelf();
        $orderCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('status', ['in' => $statuses])->willReturnSelf();
        $orderCollection->expects($this->at(2))
            ->method('setOrder')
            ->with('created_at', 'desc')->willReturnSelf();
        $this->orderCollectionFactoryInterface->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($orderCollection);
        $this->pageConfig->expects($this->atLeastOnce())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->atLeastOnce())
            ->method('set')
            ->willReturnSelf();

        $this->model = new History(
            $this->context,
            $this->orderCollectionFactory,
            $this->customerSession,
            $this->orderConfig,
            $data
        );
        $this->assertEquals($orderCollection, $this->model->getOrders());
    }
}
