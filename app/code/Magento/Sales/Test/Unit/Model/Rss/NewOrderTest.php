<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class NewOrderTest
 * @package Magento\Sales\Model\Rss
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Rss\NewOrder
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $timezoneInterface;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
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
        $this->orderFactory = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->timezoneInterface = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->dateTime = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->rssUrlBuilderInterface = $this->getMockBuilder(\Magento\Framework\App\Rss\UrlBuilderInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Sales\Model\Rss\NewOrder::class,
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

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['__sleep', '__wakeup', 'getResourceCollection', 'getIncrementId', 'getId', 'getCreatedAt'])
            ->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getId')->willReturn(1);
        $order->expects($this->once())->method('getIncrementId')->willReturn('100000001');
        $order->expects($this->once())->method('getCreatedAt')->willReturn(time());

        $collection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Collection::class)
            ->setMethods(['addAttributeToFilter', 'addAttributeToSort', 'getIterator'])
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $collection->expects($this->once())->method('addAttributeToSort')->willReturnSelf();
        $collection->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([$order]));

        $order->expects($this->once())->method('getResourceCollection')->willReturn($collection);
        $this->orderFactory->expects($this->once())->method('create')->willReturn($order);

        $this->eventManager->expects($this->once())->method('dispatch')->willReturnSelf();

        $block = $this->createPartialMock(\Magento\Sales\Block\Adminhtml\Order\Details::class, ['setOrder', 'toHtml']);
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
