<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Rss;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Rss\Signature;

/**
 * Class OrderStatusTest
 *
 * @package Magento\Sales\Model\Rss
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderStatusTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestInterface;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderStatusFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $timezoneInterface;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $order;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Signature
     */
    private $signature;

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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->urlInterface = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->requestInterface = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->orderStatusFactory =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatusFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneInterface = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->orderFactory = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(
                [
                    '__sleep',
                    '__wakeup',
                    'getIncrementId',
                    'getId',
                    'getCustomerId',
                    'load',
                    'getStatusLabel',
                    'formatPrice',
                    'getGrandTotal',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->order->expects($this->any())->method('getId')->willReturn(1);
        $this->order->expects($this->any())->method('getIncrementId')->willReturn('100000001');
        $this->order->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->order->expects($this->any())->method('getStatusLabel')->willReturn('Pending');
        $this->order->expects($this->any())->method('formatPrice')->willReturn('15.00');
        $this->order->expects($this->any())->method('getGrandTotal')->willReturn(15);
        $this->order->expects($this->any())->method('load')->with(1)->willReturnSelf();
        $this->signature = $this->createMock(Signature::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Sales\Model\Rss\OrderStatus::class,
            [
                'objectManager' => $this->objectManager,
                'urlBuilder' => $this->urlInterface,
                'request' => $this->requestInterface,
                'orderResourceFactory' => $this->orderStatusFactory,
                'localeDate' => $this->timezoneInterface,
                'orderFactory' => $this->orderFactory,
                'scopeConfig' => $this->scopeConfigInterface,
                'signature' => $this->signature,
            ]
        );
    }

    /**
     * Positive scenario.
     */
    public function testGetRssData()
    {
        $this->orderFactory->expects($this->once())->method('create')->willReturn($this->order);
        $requestData = base64_encode('{"order_id":1,"increment_id":"100000001","customer_id":1}');
        $this->signature->expects($this->never())->method('signData');
        $this->signature->expects($this->any())
            ->method('isValid')
            ->with($requestData, 'signature')
            ->willReturn(true);

        $this->requestInterface->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['data', null, $requestData],
                    ['signature', null, 'signature'],
                ]
            );

        $resource = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatus::class)
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
            ->willReturn('http://magento.com/sales/order/view/order_id/1');

        $this->assertEquals($this->feedData, $this->model->getRssData());
    }

    /**
     * Case when invalid data is provided.
     *
     */
    public function testGetRssDataWithError()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order not found.');

        $this->orderFactory->expects($this->once())->method('create')->willReturn($this->order);
        $requestData = base64_encode('{"order_id":"1","increment_id":true,"customer_id":true}');
        $this->signature->expects($this->never())->method('signData');
        $this->signature->expects($this->any())
            ->method('isValid')
            ->with($requestData, 'signature')
            ->willReturn(true);
        $this->requestInterface->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['data', null, $requestData],
                    ['signature', null, 'signature'],
                ]
            );
        $this->orderStatusFactory->expects($this->never())->method('create');
        $this->urlInterface->expects($this->never())->method('getUrl');
        $this->assertEquals($this->feedData, $this->model->getRssData());
    }

    /**
     * Case when invalid signature is provided.
     *
     */
    public function testGetRssDataWithWrongSignature()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order not found.');

        $requestData = base64_encode('{"order_id":"1","increment_id":true,"customer_id":true}');
        $this->signature->expects($this->never())
            ->method('signData');
        $this->signature->expects($this->any())
            ->method('isValid')
            ->with($requestData, 'signature')
            ->willReturn(false);
        $this->requestInterface->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['data', null, $requestData],
                    ['signature', null, 'signature'],
                ]
            );
        $this->orderStatusFactory->expects($this->never())->method('create');
        $this->urlInterface->expects($this->never())->method('getUrl');
        $this->assertEquals($this->feedData, $this->model->getRssData());
    }

    /**
     * Testing allowed getter.
     */
    public function testIsAllowed()
    {
        $this->scopeConfigInterface->expects($this->once())->method('getValue')
            ->with('rss/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->model->isAllowed());
    }

    /**
     * Test caching.
     *
     * @param string $requestData
     * @param string $result
     * @dataProvider getCacheKeyDataProvider
     */
    public function testGetCacheKey($requestData, $result)
    {
        $this->requestInterface->expects($this->any())->method('getParam')
            ->willReturnMap(
                [
                    ['data', null, $requestData],
                    ['signature', null, 'signature'],
                ]
            );
        $this->signature->expects($this->never())->method('signData');
        $this->signature->expects($this->any())
            ->method('isValid')
            ->with($requestData, 'signature')
            ->willReturn(true);
        $this->orderFactory->expects($this->once())->method('create')->willReturn($this->order);
        $this->assertEquals('rss_order_status_data_' . $result, $this->model->getCacheKey());
    }

    /**
     * Test data for caching test.
     *
     * @return array
     */
    public function getCacheKeyDataProvider()
    {
        // phpcs:disable
        return [
            [base64_encode('{"order_id":1,"increment_id":"100000001","customer_id":1}'), md5('11000000011')],
            [base64_encode('{"order_id":"1","increment_id":true,"customer_id":true}'), '']
        ];
        // phpcs:enable
    }

    /**
     * Test for cache lifetime getter.
     */
    public function testGetCacheLifetime()
    {
        $this->assertEquals(600, $this->model->getCacheLifetime());
    }
}
