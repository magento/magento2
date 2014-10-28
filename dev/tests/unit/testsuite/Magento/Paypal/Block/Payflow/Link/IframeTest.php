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
namespace Magento\Paypal\Block\Payflow\Link;

/**
 * Test for Iframe block
 *
 */
class IframeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check that isScopePrivate is false
     */
    public function testCheckIsScopePrivate()
    {
        $contextMock = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $orderFactoryMock = $this->getMock('Magento\Sales\Model\OrderFactory', ['getQuote'], [], '', false);
        $hssHelperMock = $this->getMock('Magento\Paypal\Helper\Hss', [], [], '', false);
        $paymentDataMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', ['getPayment', '__wakeup'], [], '', false);
        $paymentMock = $this->getMock('Magento\Sales\Model\Quote\Payment', [], [], '', false);

        $checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));
        $hssHelperMock->expects($this->any())
            ->method('getHssMethods')
            ->will($this->returnValue([]));

        $block = new \Magento\Paypal\Block\Payflow\Advanced\Iframe(
            $contextMock,
            $orderFactoryMock,
            $checkoutSessionMock,
            $hssHelperMock,
            $paymentDataMock
        );

        $this->assertFalse($block->isScopePrivate());
    }
}
