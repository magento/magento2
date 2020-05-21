<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order\Info\Buttons;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Info\Buttons\Rss;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Rss\Signature;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RssTest extends TestCase
{
    /**
     * @var Rss
     */
    protected $rss;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var MockObject
     */
    protected $orderFactory;

    /**
     * @var UrlBuilderInterface|MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var MockObject|Signature
     */
    private $signature;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->orderFactory = $this->createPartialMock(OrderFactory::class, ['create']);
        $this->urlBuilderInterface = $this->getMockForAbstractClass(UrlBuilderInterface::class);
        $this->scopeConfigInterface = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->signature = $this->createMock(Signature::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            Rss::class,
            [
                'request' => $request,
                'orderFactory' => $this->orderFactory,
                'rssUrlBuilder' => $this->urlBuilderInterface,
                'scopeConfig' => $this->scopeConfigInterface,
                'signature' => $this->signature,
            ]
        );
    }

    public function testGetLink()
    {
        $order = $this->getMockBuilder(Order::class)
            ->setMethods(['getId', 'getCustomerId', 'getIncrementId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->once())->method('load')->willReturnSelf();
        $order->expects($this->once())->method('getId')->willReturn(1);
        $order->expects($this->once())->method('getCustomerId')->willReturn(1);
        $order->expects($this->once())->method('getIncrementId')->willReturn('100000001');

        $this->orderFactory->expects($this->once())->method('create')->willReturn($order);
        $data = base64_encode(json_encode(['order_id' => 1, 'increment_id' => '100000001', 'customer_id' => 1]));
        $signature = '651932dfc862406b72628d95623bae5ea18242be757b3493b337942d61f834be';
        $this->signature->expects($this->once())->method('signData')->willReturn($signature);
        $link = 'http://magento.com/rss/feed/index/type/order_status?data=' . $data . '&signature=' . $signature;
        $this->urlBuilderInterface->expects($this->once())->method('getUrl')
            ->with(
                [
                    'type' => 'order_status',
                    '_secure' => true,
                    '_query' => ['data' => $data, 'signature' => $signature],
                ]
            )
            ->willReturn($link);

        $this->assertEquals($link, $this->rss->getLink());
    }

    public function testGetLabel()
    {
        $this->assertEquals('Subscribe to Order Status', $this->rss->getLabel());
    }

    public function testIsRssAllowed()
    {
        $this->scopeConfigInterface->expects($this->once())->method('isSetFlag')
            ->with('rss/order/status', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->rss->isRssAllowed());
    }
}
