<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Block\Onepage;

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
     * @var \Magento\Sales\Model\OrderFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->orderConfig = $this->getMock('Magento\Sales\Model\Order\Config', [], [], '', false);
        $this->orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->checkoutSession = $this->getMock('Magento\Checkout\Model\Session', ['getLastOrderId'], [], '', false);

        $this->block = $objectManager->getObject(
            'Magento\Checkout\Block\Onepage\Success',
            [
                'orderConfig' => $this->orderConfig,
                'orderFactory' => $this->orderFactory,
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
     * @param array $invisibleStatuses
     * @param string $orderStatus
     * @param bool $expectedResult
     */
    public function testToHtmlOrderVisibleOnFront(array $invisibleStatuses, $orderStatus, $expectedResult)
    {
        $orderId = 5;
        $order = $this->getMock('Magento\Sales\Model\Order', ['getId', '__wakeup', 'load', 'getStatus'], [], '', false);

        $order->expects($this->any())
            ->method('load')
            ->with($orderId)
            ->will($this->returnValue($order));
        $order->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($orderId));
        $order->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue($orderStatus));

        $this->checkoutSession->expects($this->once())
            ->method('getLastOrderId')
            ->will($this->returnValue($orderId));
        $this->orderConfig->expects($this->any())
            ->method('getInvisibleOnFrontStatuses')
            ->will($this->returnValue($invisibleStatuses));
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($order));

        $this->block->toHtml();

        $this->assertEquals($expectedResult, $this->block->getIsOrderVisible());
    }

    public function invisibleStatusesProvider()
    {
        return [
            [['status1', 'status2'], 'status1', false],
            [['status1', 'status2'], 'status3', true]
        ];
    }
}
