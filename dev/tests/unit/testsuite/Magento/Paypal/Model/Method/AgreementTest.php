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
namespace Magento\Paypal\Model\Method;

class AgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Paypal\Model\Method\Agreement
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_apiNvpMock;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $paypalConfigMock = $this->getMockBuilder(
            '\Magento\Paypal\Model\Config'
        )->disableOriginalConstructor()->setMethods(
            array('getConfigValue')
        )->getMock();
        $this->_apiNvpMock = $this->getMockBuilder(
            '\Magento\Paypal\Model\Api\Nvp'
        )->disableOriginalConstructor()->setMethods(
            array('callDoReferenceTransaction', 'callGetTransactionDetails')
        )->getMock();
        $proMock = $this->getMockBuilder(
            '\Magento\Paypal\Model\Pro'
        )->setMethods(
            array('getApi', 'setMethod', 'getConfig', 'importPaymentInfo')
        )->disableOriginalConstructor()->getMock();
        $proMock->expects($this->any())->method('getApi')->will($this->returnValue($this->_apiNvpMock));
        $proMock->expects($this->any())->method('getConfig')->will($this->returnValue($paypalConfigMock));

        $billingAgreementMock = $this->getMockBuilder(
            '\Magento\Paypal\Model\Billing\Agreement'
        )->disableOriginalConstructor()->setMethods(
            array('load', '__wakeup')
        )->getMock();
        $billingAgreementMock->expects($this->any())->method('load')->will($this->returnValue($billingAgreementMock));

        $agreementFactoryMock = $this->getMockBuilder(
            '\Magento\Paypal\Model\Billing\AgreementFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();
        $agreementFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($billingAgreementMock)
        );

        $cartMock = $this->getMockBuilder('\Magento\Paypal\Model\Cart')->disableOriginalConstructor()->getMock();
        $cartFactoryMock = $this->getMockBuilder(
            '\Magento\Paypal\Model\CartFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();
        $cartFactoryMock->expects($this->any())->method('create')->will($this->returnValue($cartMock));

        $arguments = array(
            'agreementFactory' => $agreementFactoryMock,
            'cartFactory' => $cartFactoryMock,
            'data' => array($proMock)
        );

        $this->_model = $this->_helper->getObject('Magento\Paypal\Model\Method\Agreement', $arguments);
    }

    public function testAuthorizeWithBaseCurrency()
    {
        $payment = $this->getMockBuilder(
            '\Magento\Sales\Model\Order\Payment'
        )->disableOriginalConstructor()->setMethods(
            array('__wakeup')
        )->getMock();
        $order = $this->getMockBuilder(
            'Magento\Sales\Model\Order'
        )->disableOriginalConstructor()->setMethods(
            array('__wakeup')
        )->getMock();
        $order->setBaseCurrencyCode('USD');
        $payment->setOrder($order);

        $this->_model->authorize($payment, 10.00);
        $this->assertEquals($order->getBaseCurrencyCode(), $this->_apiNvpMock->getCurrencyCode());
    }
}
