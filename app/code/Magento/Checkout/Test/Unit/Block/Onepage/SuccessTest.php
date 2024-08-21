<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Onepage;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuccessTest extends TestCase
{
    /**
     * @var Success
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $layout;

    /**
     * @var Config|MockObject
     */
    protected $orderConfig;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSession;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->orderConfig = $this->createMock(Config::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->layout = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                $this->stringContains(
                    'advanced/modules_disable_output/'
                ),
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(false);

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLayout', 'getEventManager', 'getUrlBuilder', 'getScopeConfig', 'getStoreManager'])
            ->getMock();
        $context->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $context->expects($this->any())->method('getUrlBuilder')->willReturn($urlBuilder);
        $context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $context->expects($this->any())->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->block = $objectManager->getObject(
            Success::class,
            [
                'context' => $context,
                'orderConfig' => $this->orderConfig,
                'checkoutSession' => $this->checkoutSession
            ]
        );
    }

    public function testGetAdditionalInfoHtml()
    {
        $layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $layout->expects(
            $this->once()
        )->method(
            'renderElement'
        )->with(
            'order.success.additional.info'
        )->willReturn(
            'AdditionalInfoHtml'
        );
        $this->block->setLayout($layout);
        $this->assertEquals('AdditionalInfoHtml', $this->block->getAdditionalInfoHtml());
    }

    /**
     * @dataProvider invisibleStatusesProvider
     *
     * @param array $invisibleStatuses
     * @param bool $expectedResult
     */
    public function testToHtmlOrderVisibleOnFront(array $invisibleStatuses, $expectedResult)
    {
        $orderId = 5;
        $realOrderId = 100003332;
        $status = Order::STATE_PENDING_PAYMENT;

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession->expects($this->once())
            ->method('getLastRealOrder')
            ->willReturn($order);
        $order->expects($this->atLeastOnce())
            ->method('getEntityId')
            ->willReturn($orderId);
        $order->expects($this->atLeastOnce())
            ->method('getIncrementId')
            ->willReturn($realOrderId);
        $order->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($status);

        $this->orderConfig->expects($this->any())
            ->method('getInvisibleOnFrontStatuses')
            ->willReturn($invisibleStatuses);

        $this->block->toHtml();

        $this->assertEquals($expectedResult, $this->block->getIsOrderVisible());
    }

    /**
     * @return array
     */
    public static function invisibleStatusesProvider()
    {
        return [
            [[Order::STATE_PENDING_PAYMENT, 'status2'],  false],
            [['status1', 'status2'], true]
        ];
    }

    public function testGetContinueUrl()
    {
        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn('Expected Result');

        $this->assertEquals('Expected Result', $this->block->getContinueUrl());
    }
}
