<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Rss;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatusFactory;
use Magento\Sales\Model\Rss\OrderStatus;
use Magento\Sales\Model\Rss\Signature;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderStatusTest extends TestCase
{
    /**
     * @var OrderStatus
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlInterface;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestInterface;

    /**
     * @var MockObject
     */
    protected $orderStatusFactory;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneInterface;

    /**
     * @var MockObject
     */
    protected $orderFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Sales\Model\Order|MockObject
     */
    protected $order;

    /**
     * @var MockObject|Signature
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
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->urlInterface = $this->getMockForAbstractClass(UrlInterface::class);
        $this->requestInterface = $this->getMockForAbstractClass(RequestInterface::class);
        $this->orderStatusFactory =
            $this->getMockBuilder(OrderStatusFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->timezoneInterface = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->orderFactory = $this->createPartialMock(OrderFactory::class, ['create']);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->order = $this->getMockBuilder(Order::class)
            ->setMethods(
                [
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
            OrderStatus::class,
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
     */
    public function testGetRssDataWithError()
    {
        $this->expectException('InvalidArgumentException');
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
     */
    public function testGetRssDataWithWrongSignature()
    {
        $this->expectException('InvalidArgumentException');
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
            ->with('rss/order/status', ScopeInterface::SCOPE_STORE)
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
