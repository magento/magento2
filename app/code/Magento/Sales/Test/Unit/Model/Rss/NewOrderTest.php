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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuiler;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneInterface;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->orderFactory = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $this->urlBuiler = $this->createMock(\Magento\Framework\UrlInterface::class);
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
                'urlBuilder' => $this->urlBuiler,
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
        $this->dateTime->expects($this->once())->method('formatDate')->will($this->returnValue(date('Y-m-d H:i:s')));

        $this->rssUrlBuilderInterface->expects($this->once())->method('getUrl')
            ->with(['_secure' => true, '_nosecret' => true, 'type' => 'new_order'])
            ->will($this->returnValue('http://magento.com/backend/rss/feed/index/type/new_order'));

        $this->timezoneInterface->expects($this->once())->method('formatDate')
            ->will($this->returnValue('2014-09-10 17:39:50'));

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['__sleep', '__wakeup', 'getResourceCollection', 'getIncrementId', 'getId', 'getCreatedAt'])
            ->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getId')->will($this->returnValue(1));
        $order->expects($this->once())->method('getIncrementId')->will($this->returnValue('100000001'));
        $order->expects($this->once())->method('getCreatedAt')->will($this->returnValue(time()));

        $collection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Collection::class)
            ->setMethods(['addAttributeToFilter', 'addAttributeToSort', 'getIterator'])
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('addAttributeToSort')->will($this->returnSelf());
        $collection->expects($this->once())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$order])));

        $order->expects($this->once())->method('getResourceCollection')->will($this->returnValue($collection));
        $this->orderFactory->expects($this->once())->method('create')->will($this->returnValue($order));

        $this->eventManager->expects($this->once())->method('dispatch')->will($this->returnSelf());

        $block = $this->createPartialMock(\Magento\Sales\Block\Adminhtml\Order\Details::class, ['setOrder', 'toHtml']);
        $block->expects($this->once())->method('setOrder')->with($order)->will($this->returnSelf());
        $block->expects($this->once())->method('toHtml')->will($this->returnValue('Order Description'));

        $this->layout->expects($this->once())->method('getBlockSingleton')->will($this->returnValue($block));
        $this->urlBuiler->expects($this->once())->method('getUrl')
            ->will($this->returnValue('http://magento.com/sales/order/view/order_id/1'));
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
