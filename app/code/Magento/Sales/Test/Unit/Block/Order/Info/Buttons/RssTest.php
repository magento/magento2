<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Order\Info\Buttons;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Rss\Signature;

/**
 * Class RssTest
 * @package Magento\Sales\Block\Order\Info\Buttons
 */
class RssTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\Info\Buttons\Rss
     */
    protected $rss;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Signature
     */
    private $signature;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->orderFactory = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $this->urlBuilderInterface = $this->createMock(\Magento\Framework\App\Rss\UrlBuilderInterface::class);
        $this->scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->signature = $this->createMock(Signature::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rss = $this->objectManagerHelper->getObject(
            \Magento\Sales\Block\Order\Info\Buttons\Rss::class,
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
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getId', 'getCustomerId', 'getIncrementId', 'load', '__wakeup', '__sleep'])
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
        $link = 'http://magento.com/rss/feed/index/type/order_status?data=' . $data .'&signature='.$signature;
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
            ->with('rss/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->assertTrue($this->rss->isRssAllowed());
    }
}
