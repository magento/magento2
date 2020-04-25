<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->orderFactory = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->timezoneInterface = $this->createMock(TimezoneInterface::class);
        $this->dateTime = $this->createMock(DateTime::class);
        $this->scopeConfigInterface = $this->createMock(ScopeConfigInterface::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->layout = $this->createMock(LayoutInterface::class);
        $this->rssUrlBuilderInterface = $this->getMockBuilder(UrlBuilderInterface::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()->getMock();
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
        $this->dateTime->expects($this->once())->method('formatDate')->will($this->returnValue(date('Y-m-d H:i:s')));

        $this->rssUrlBuilderInterface->expects($this->once())->method('getUrl')
            ->with(['_secure' => true, '_nosecret' => true, 'type' => 'new_order'])
            ->will($this->returnValue('http://magento.com/backend/rss/feed/index/type/new_order'));

        $this->timezoneInterface->expects($this->once())->method('formatDate')
            ->will($this->returnValue('2014-09-10 17:39:50'));

        $order = $this->getMockBuilder(Order::class)
            ->setMethods(['__sleep', '__wakeup', 'getResourceCollection', 'getIncrementId', 'getId', 'getCreatedAt'])
            ->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getId')->will($this->returnValue(1));
        $order->expects($this->once())->method('getIncrementId')->will($this->returnValue('100000001'));
        $order->expects($this->once())->method('getCreatedAt')->will($this->returnValue(time()));

        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['addAttributeToFilter', 'addAttributeToSort', 'getIterator'])
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $collection->expects($this->once())->method('addAttributeToSort')->will($this->returnSelf());
        $collection->expects($this->once())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$order])));

        $order->expects($this->once())->method('getResourceCollection')->will($this->returnValue($collection));
        $this->orderFactory->expects($this->once())->method('create')->will($this->returnValue($order));

        $this->eventManager->expects($this->once())->method('dispatch')->will($this->returnSelf());

        $block = $this->createPartialMock(Details::class, ['setOrder', 'toHtml']);
        $block->expects($this->once())->method('setOrder')->with($order)->will($this->returnSelf());
        $block->expects($this->once())->method('toHtml')->will($this->returnValue('Order Description'));

        $this->layout->expects($this->once())->method('getBlockSingleton')->will($this->returnValue($block));
        $this->urlBuilder->expects($this->once())->method('getUrl')
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
