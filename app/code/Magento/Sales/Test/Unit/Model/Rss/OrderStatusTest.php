<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class OrderStatusTest
 * @package Magento\Sales\Model\Rss
 */
class OrderStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Rss\OrderStatus
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestInterface;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneInterface;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;
    /**
     * @var array
     */
    protected $feedData = [
        'title' => 'Order # 100000001 Notification(s)',
        'link' => 'http://magento.com/sales/order/view/order_id/1',
        'description' => 'Order # 100000001 Notification(s)',
        'charset' => 'UTF-8',
        'entries' => [
            [
                'title' => 'Details for Order #100000001',
                'link' => 'http://magento.com/sales/order/view/order_id/1',
                'description' => '<p>Notified Date: <br/>Comment: Some comment<br/></p>',
            ],
            [
                'title' => 'Order #100000001 created at ',
                'link' => 'http://magento.com/sales/order/view/order_id/1',
                'description' => '<p>Current Status: Pending<br/>Total: 15.00<br/></p>'
            ],
        ],
    ];

    protected function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->urlInterface = $this->getMock('Magento\Framework\UrlInterface');
        $this->requestInterface = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->orderStatusFactory =
            $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatusFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneInterface = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $this->order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods([
                '__sleep',
                '__wakeup',
                'getIncrementId',
                'getId',
                'getCustomerId',
                'load',
                'getStatusLabel',
                'formatPrice',
                'getGrandTotal',
            ])->disableOriginalConstructor()->getMock();
        $this->order->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->order->expects($this->any())->method('getIncrementId')->will($this->returnValue('100000001'));
        $this->order->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));
        $this->order->expects($this->any())->method('getStatusLabel')->will($this->returnValue('Pending'));
        $this->order->expects($this->any())->method('formatPrice')->will($this->returnValue('15.00'));
        $this->order->expects($this->any())->method('getGrandTotal')->will($this->returnValue(15));
        $this->order->expects($this->any())->method('load')->with(1)->will($this->returnSelf());

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Sales\Model\Rss\OrderStatus',
            [
                'objectManager' => $this->objectManager,
                'urlBuilder' => $this->urlInterface,
                'request' => $this->requestInterface,
                'orderResourceFactory' => $this->orderStatusFactory,
                'localeDate' => $this->timezoneInterface,
                'orderFactory' => $this->orderFactory,
                'scopeConfig' => $this->scopeConfigInterface
            ]
        );
    }

    public function testGetRssData()
    {
        $this->orderFactory->expects($this->once())->method('create')->willReturn($this->order);
        $requestData = base64_encode('{"order_id":1,"increment_id":"100000001","customer_id":1}');

        $this->requestInterface->expects($this->any())->method('getParam')->with('data')->willReturn($requestData);

        $resource = $this->getMockBuilder('\Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatus')
            ->setMethods(['getAllCommentCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $comment = [
            'entity_type_code' => 'order',
            'increment_id' => '100000001',
            'created_at' => '2014-10-09 18:25:50',
            'comment' => 'Some comment',
        ];
        $resource->expects($this->once())->method('getAllCommentCollection')->willReturn([$comment]);
        $this->orderStatusFactory->expects($this->once())->method('create')->willReturn($resource);
        $this->urlInterface->expects($this->any())->method('getUrl')
            ->with('sales/order/view', ['order_id' => 1])
            ->will($this->returnValue('http://magento.com/sales/order/view/order_id/1'));

        $this->assertEquals($this->feedData, $this->model->getRssData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Order not found.
     */
    public function testGetRssDataWithError()
    {
        $this->orderFactory->expects($this->once())->method('create')->willReturn($this->order);

        $requestData = base64_encode('{"order_id":"1","increment_id":true,"customer_id":true}');

        $this->requestInterface->expects($this->any())->method('getParam')->with('data')->willReturn($requestData);

        $this->orderStatusFactory->expects($this->never())->method('create');

        $this->urlInterface->expects($this->never())->method('getUrl');

        $this->assertEquals($this->feedData, $this->model->getRssData());
    }

    public function testIsAllowed()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue(true));
        $this->assertTrue($this->model->isAllowed());
    }

    /**
     * @param string $requestData
     * @param string $result
     * @dataProvider getCacheKeyDataProvider
     */
    public function testGetCacheKey($requestData, $result)
    {
        $this->requestInterface->expects($this->any())->method('getParam')
            ->with('data')
            ->will($this->returnValue($requestData));
        $this->orderFactory->expects($this->once())->method('create')->will($this->returnValue($this->order));
        $this->assertEquals('rss_order_status_data_' . $result, $this->model->getCacheKey());
    }

    /**
     * @return array
     */
    public function getCacheKeyDataProvider()
    {
        return [
            [base64_encode('{"order_id":1,"increment_id":"100000001","customer_id":1}'), md5('11000000011')],
            [base64_encode('{"order_id":"1","increment_id":true,"customer_id":true}'), '']
        ];
    }

    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->model->getCacheLifetime());
    }
}
