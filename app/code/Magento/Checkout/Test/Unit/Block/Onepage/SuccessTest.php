<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Onepage;

use Magento\Sales\Model\Order;

/**
 * Class SuccessTest
 * @package Magento\Checkout\Block\Onepage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuccessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Block\Onepage\Success
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderConfig = $this->createMock(\Magento\Sales\Model\Order\Config::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                $this->stringContains(
                    'advanced/modules_disable_output/'
                ),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->will($this->returnValue(false));

        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLayout', 'getEventManager', 'getUrlBuilder', 'getScopeConfig', 'getStoreManager'])
            ->getMock();
        $context->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));
        $context->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $context->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $context->expects($this->any())->method('getScopeConfig')->will($this->returnValue($scopeConfig));
        $context->expects($this->any())->method('getStoreManager')->will($this->returnValue($this->storeManagerMock));

        $this->block = $objectManager->getObject(
            \Magento\Checkout\Block\Onepage\Success::class,
            [
                'context' => $context,
                'orderConfig' => $this->orderConfig,
                'checkoutSession' => $this->checkoutSession
            ]
        );
    }

    public function testGetAdditionalInfoHtml()
    {
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layout->expects(
            $this->once()
        )->method(
            'renderElement'
        )->with(
            'order.success.additional.info'
        )->will(
            $this->returnValue('AdditionalInfoHtml')
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

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
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

    public function invisibleStatusesProvider()
    {
        return [
            [[Order::STATE_PENDING_PAYMENT, 'status2'],  false],
            [['status1', 'status2'], true]
        ];
    }

    public function testGetContinueUrl()
    {
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())->method('getBaseUrl')->will($this->returnValue('Expected Result'));

        $this->assertEquals('Expected Result', $this->block->getContinueUrl());
    }
}
