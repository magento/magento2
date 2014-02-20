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
 * @category    Magento
 * @package     Magento_Paypal
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Paypal\Model\Ipn
 */
namespace Magento\Paypal\Model;

class IpnTest extends \PHPUnit_Framework_TestCase
{
    const REQUEST_MC_GROSS = 38.12;

    /**
     * @var \Magento\Paypal\Model\Ipn
     */
    protected $_ipn;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_ipn = $objectHelper->getObject('Magento\Paypal\Model\Ipn');
    }

    /**
     * Prepare order property for ipn model
     *
     * @param \Magento\Paypal\Model\Ipn|\PHPUnit_Framework_MockObject_MockObject $ipn
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _prepareIpnOrderProperty($ipn)
    {
        // Create payment and order mocks
        $payment = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));

        // Set order to ipn
        $orderProperty = new \ReflectionProperty('Magento\Paypal\Model\Ipn', '_order');
        $orderProperty->setAccessible(true);
        $orderProperty->setValue($ipn, $order);

        return $order;
    }

    public function testLegacyRegisterPaymentAuthorization()
    {
        $order = $this->_prepareIpnOrderProperty($this->_ipn);
        $order->expects($this->once())
            ->method('canFetchPaymentReviewUpdate')
            ->will($this->returnValue(false));
        $payment = $order->getPayment();
        $payment->expects($this->once())
            ->method('registerAuthorizationNotification')
            ->with($this->equalTo(self::REQUEST_MC_GROSS));
        $payment->expects($this->any())
            ->method('__call')
            ->will($this->returnSelf());

        // Create info mock
        $info = $this->getMock('Magento\Paypal\Model\Info');
        $info->expects($this->once())
            ->method('importToPayment');

        // Set request to ipn
        $requestProperty = new \ReflectionProperty('Magento\Paypal\Model\Ipn', '_request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_ipn, array(
            'mc_gross' => self::REQUEST_MC_GROSS,
        ));

        // Set info to ipn
        $infoProperty = new \ReflectionProperty('Magento\Paypal\Model\Ipn', '_info');
        $infoProperty->setAccessible(true);
        $infoProperty->setValue($this->_ipn, $info);

        $testMethod = new \ReflectionMethod('Magento\Paypal\Model\Ipn', '_registerPaymentAuthorization');
        $testMethod->setAccessible(true);
        $testMethod->invoke($this->_ipn);
    }

    public function testPaymentReviewRegisterPaymentAuthorization()
    {
        $order = $this->_prepareIpnOrderProperty($this->_ipn);
        $order->expects($this->once())
            ->method('canFetchPaymentReviewUpdate')
            ->will($this->returnValue(true));
        $order->getPayment()->expects($this->once())
            ->method('registerPaymentReviewAction')
            ->with(
                $this->equalTo(\Magento\Sales\Model\Order\Payment::REVIEW_ACTION_UPDATE),
                $this->equalTo(true)
            );

        $testMethod = new \ReflectionMethod('Magento\Paypal\Model\Ipn', '_registerPaymentAuthorization');
        $testMethod->setAccessible(true);
        $testMethod->invoke($this->_ipn);
    }
}
