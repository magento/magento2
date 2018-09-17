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
 */
class SuccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Onepage\Success
     */
    protected $block;

    /**
     * @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderConfig = $this->getMock('Magento\Sales\Model\Order\Config', [], [], '', false);

        $this->checkoutSession = $this->getMockBuilder(
            'Magento\Checkout\Model\Session'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $objectManager->getObject(
            'Magento\Checkout\Block\Onepage\Success',
            [
                'orderConfig' => $this->orderConfig,
                'checkoutSession' => $this->checkoutSession
            ]
        );
    }

    public function testGetAdditionalInfoHtml()
    {
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);
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

        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
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
    public function invisibleStatusesProvider()
    {
        return [
            [[Order::STATE_PENDING_PAYMENT, 'status2'],  false],
            [['status1', 'status2'], true]
        ];
    }
}
