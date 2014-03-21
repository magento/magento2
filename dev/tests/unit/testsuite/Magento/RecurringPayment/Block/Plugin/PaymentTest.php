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
namespace Magento\RecurringPayment\Block\Plugin;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\RecurringPayment\Block\Plugin\Payment */
    protected $payment;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\RecurringPayment\Model\Quote\Filter|\PHPUnit_Framework_MockObject_MockObject */
    protected $filterMock;

    protected function setUp()
    {
        $this->sessionMock = $this->getMock('Magento\Checkout\Model\Session', array(), array(), '', false);
        $this->filterMock = $this->getMock('Magento\RecurringPayment\Model\Quote\Filter');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->payment = $this->objectManagerHelper->getObject(
            'Magento\RecurringPayment\Block\Plugin\Payment',
            array('session' => $this->sessionMock, 'filter' => $this->filterMock)
        );
    }

    public function testAfterGetOptions()
    {
        $quote = $this->getMockBuilder('Magento\Sales\Model\Quote')->disableOriginalConstructor()->getMock();
        $this->sessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quote));
        $this->filterMock->expects(
            $this->once()
        )->method(
            'hasRecurringItems'
        )->with(
            $quote
        )->will(
            $this->returnValue(true)
        );

        $this->assertArrayHasKey(
            'hasRecurringItems',
            $this->payment->afterGetOptions(
                $this->getMock('\Magento\Checkout\Block\Onepage\Payment', array(), array(), '', false),
                array()
            )
        );
    }
}
