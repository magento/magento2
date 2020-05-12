<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Rss;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\Details;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\Rss\NewOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewOrderTest extends TestCase
{
    /**
     * @var NewOrder
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject
     */
    protected $orderFactory;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneInterface;

    /**
     * @var DateTime|MockObject
     */
    protected $dateTime;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $rssUrlBuilderInterface;

    /**
     * @var array
     */
    protected $feedData = [
        'title' => 'New Orders',
        'link' => 'http://magento.com/backend/rss/feed/index/type/new_order',
        'description' => 'New Orders',
        'charset' => 'UTF-8',
        'entries' => [
            [
                'title' => 'Order #100000001 created at 2014-09-10 17:39:50',
                'link' => 'http://magento.com/sales/order/view/order_id/1',
                'description' => 'Order Description',
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->orderFactory = $this->createPartialMock(OrderFactory::class, ['create']);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->timezoneInterface = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->dateTime = $this->createMock(DateTime::class);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->rssUrlBuilderInterface = $this->getMockBuilder(UrlBuilderInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            NewOrder::class,
            [
                'orderFactory' => $this->orderFactory,
                'urlBuilder' => $this->urlBuilder,
                'rssUrlBuilder' => $this->rssUrlBuilderInterface,
                'localeDate' => $this->timezoneInterface,
                'dateTime' => $this->dateTime,
                'scopeConfig' => $this->scopeConfigInterface,
                'eventManager' => $this->eventManager,
                'layout' => $this->layout
            ]
        );
    }

    public function testIsAllowed()
    {
        $this->assertTrue($this->model->isAllowed());
    }

    public function testGetData()
    {
        $this->dateTime->expects($this->once())->method('formatDate')->willReturn(date('Y-m-d H:i:s'));

        $this->rssUrlBuilderInterface->expects($this->once())->method('getUrl')
            ->with(['_secure' => true, '_nosecret' => true, 'type' => 'new_order'])
            ->willReturn('http://magento.com/backend/rss/feed/index/type/new_order');

        $this->timezoneInterface->expects($this->once())->method('formatDate')
            ->willReturn('2014-09-10 17:39:50');

        $order = $this->getMockBuilder(Order::class)
            ->setMethods(['getResourceCollection', 'getIncrementId', 'getId', 'getCreatedAt'])
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->once())->method('getId')->willReturn(1);
        $order->expects($this->once())->method('getIncrementId')->willReturn('100000001');
        $order->expects($this->once())->method('getCreatedAt')->willReturn(time());

        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['addAttributeToFilter', 'addAttributeToSort', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $collection->expects($this->once())->method('addAttributeToSort')->willReturnSelf();
        $collection->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([$order]));

        $order->expects($this->once())->method('getResourceCollection')->willReturn($collection);
        $this->orderFactory->expects($this->once())->method('create')->willReturn($order);

        $this->eventManager->expects($this->once())->method('dispatch')->willReturnSelf();

        $block = $this->getMockBuilder(Details::class)
            ->addMethods(['setOrder'])
            ->onlyMethods(['toHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $block->expects($this->once())->method('setOrder')->with($order)->willReturnSelf();
        $block->expects($this->once())->method('toHtml')->willReturn('Order Description');

        $this->layout->expects($this->once())->method('getBlockSingleton')->willReturn($block);
        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->willReturn('http://magento.com/sales/order/view/order_id/1');
        $this->assertEquals($this->feedData, $this->model->getRssData());
    }

    public function testGetCacheKey()
    {
        $this->assertEquals('rss_new_orders_data', $this->model->getCacheKey());
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(60, $this->model->getCacheLifetime());
    }

    public function getFeeds()
    {
        $this->assertEmpty($this->model->getFeeds());
    }
}
