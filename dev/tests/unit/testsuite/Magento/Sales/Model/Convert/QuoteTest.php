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

namespace Magento\Sales\Model\Convert;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Sales\Model\Quote\Address;

/**
 * Test class for \Magento\Sales\Model\Order
 */
class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Convert\Quote
     */
    protected $quote;

    protected function setUp()
    {
        $orderPaymentMock = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            array('setStoreId', 'setCustomerPaymentId', '__wakeup'),
            array(),
            '',
            false
        );
        $orderPaymentMock->expects(
            $this->any()
        )->method(
            'setStoreId'
        )->will(
            $this->returnValue(
                $orderPaymentMock
            )
        );
        $orderPaymentMock->expects(
            $this->any()
        )->method(
            'setCustomerPaymentId'
        )->will(
            $this->returnValue(
                $orderPaymentMock
            )
        );
        $orderPaymentFactoryMock = $this->getMock(
            'Magento\Sales\Model\Order\PaymentFactory',
            array('create'),
            array(),
            '',
            false
        );
        $orderPaymentFactoryMock->expects($this->any())->method('create')->will($this->returnValue($orderPaymentMock));

        $objectCopyServiceMock = $this->getMock('Magento\Framework\Object\Copy', array(), array(), '', false);
        $objectManager = new ObjectManager($this);
        $this->quote = $objectManager->getObject(
            'Magento\Sales\Model\Convert\Quote',
            array(
                'orderPaymentFactory' => $orderPaymentFactoryMock,
                'objectCopyService' => $objectCopyServiceMock
            )
        );
    }

    public function testPaymentToOrderPayment()
    {
        $payment = $this->getMock('Magento\Sales\Model\Quote\Payment', array(), array(), '', false);
        $title = new \Magento\Framework\Object(['title' => 'some title']);
        $payment->expects($this->any())->method('getMethodInstance')->will($this->returnValue($title));
        $this->assertEquals(
            ['method_title' => 'some title'],
            $this->quote->paymentToOrderPayment($payment)->getAdditionalInformation()
        );
    }
}
