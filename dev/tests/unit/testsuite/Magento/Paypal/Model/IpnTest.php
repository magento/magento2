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

/**
 * Test class for \Magento\Paypal\Model\Ipn
 */
namespace Magento\Paypal\Model;

class IpnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Ipn
     */
    protected $_ipn;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paypalInfo;

    protected function setUp()
    {
        $methods = [
            'create',
            'loadByIncrementId',
            'canFetchPaymentReviewUpdate',
            'getId',
            'getPayment',
            'getMethod',
            'getStoreId',
            'registerPaymentReviewAction',
            'getAdditionalInformation',
            'getEmailSent',
            'save'
        ];
        $this->_orderMock = $this->getMock('Magento\Sales\Model\OrderFactory', $methods, [], '', false);
        $this->_orderMock->expects($this->any())->method('create')->will($this->returnSelf());
        $this->_orderMock->expects($this->any())->method('loadByIncrementId')->will($this->returnSelf());
        $this->_orderMock->expects($this->any())->method('getId')->will($this->returnSelf());
        $this->_orderMock->expects($this->any())->method('getMethod')->will($this->returnSelf());
        $this->_orderMock->expects($this->any())->method('getStoreId')->will($this->returnSelf());
        $this->_orderMock->expects($this->any())->method('getEmailSent')->will($this->returnValue(true));

        $configFactory = $this->getMock(
            'Magento\Paypal\Model\ConfigFactory',
            ['create', 'isMethodActive', 'isMethodAvailable', 'getConfigValue', 'getPaypalUrl'],
            [],
            '',
            false
        );
        $configFactory->expects($this->any())->method('create')->will($this->returnSelf());
        $configFactory->expects($this->any())->method('isMethodActive')->will($this->returnValue(true));
        $configFactory->expects($this->any())->method('isMethodAvailable')->will($this->returnValue(true));
        $configFactory->expects($this->any())->method('getConfigValue')->will($this->returnValue(null));
        $configFactory->expects($this->any())->method('getPaypalUrl')->will($this->returnValue('http://paypal_url'));

        $curlFactory = $this->getMock(
            'Magento\Framework\HTTP\Adapter\CurlFactory',
            ['create', 'setConfig', 'write', 'read'],
            [],
            '',
            false
        );
        $curlFactory->expects($this->any())->method('create')->will($this->returnSelf());
        $curlFactory->expects($this->any())->method('setConfig')->will($this->returnSelf());
        $curlFactory->expects($this->any())->method('write')->will($this->returnSelf());
        $curlFactory->expects($this->any())->method('read')->will($this->returnValue(
            '
                VERIFIED'
        ));
        $this->_paypalInfo = $this->getMock(
            'Magento\Paypal\Model\Info',
            ['importToPayment', 'getMethod', 'getAdditionalInformation'],
            [],
            '',
            false
        );
        $this->_paypalInfo->expects($this->any())->method('getMethod')->will($this->returnValue('some_method'));
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_ipn = $objectHelper->getObject('Magento\Paypal\Model\Ipn',
            [
                'configFactory' => $configFactory,
                'logAdapterFactory' => $this->getMock('Magento\Framework\Logger\AdapterFactory', [], [], '', false),
                'curlFactory' => $curlFactory,
                'orderFactory' => $this->_orderMock,
                'paypalInfo' => $this->_paypalInfo,
                'data' => ['payment_status' => 'Pending', 'pending_reason' => 'authorization']
            ]
        );
    }

    public function testLegacyRegisterPaymentAuthorization()
    {
        $this->_orderMock->expects($this->any())->method('canFetchPaymentReviewUpdate')->will(
            $this->returnValue(false)
        );
        $methods = [
            'setPreparedMessage',
            '__wakeup',
            'setTransactionId',
            'setParentTransactionId',
            'setIsTransactionClosed',
            'registerAuthorizationNotification'
        ];
        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', $methods, [], '', false);
        $payment->expects($this->any())->method('setPreparedMessage')->will($this->returnSelf());
        $payment->expects($this->any())->method('setTransactionId')->will($this->returnSelf());
        $payment->expects($this->any())->method('setParentTransactionId')->will($this->returnSelf());
        $payment->expects($this->any())->method('setIsTransactionClosed')->will($this->returnSelf());
        $this->_orderMock->expects($this->any())->method('getPayment')->will($this->returnValue($payment));
        $this->_orderMock->expects($this->any())->method('getAdditionalInformation')->will($this->returnValue(array()));

        $this->_paypalInfo->expects($this->once())->method('importToPayment');
        $this->_ipn->processIpnRequest();
    }

    public function testPaymentReviewRegisterPaymentAuthorization()
    {
        $this->_orderMock->expects($this->any())->method('getPayment')->will($this->returnSelf());
        $this->_orderMock->expects($this->any())->method('canFetchPaymentReviewUpdate')->will($this->returnValue(true));
        $this->_orderMock->expects($this->once())->method('registerPaymentReviewAction')->with(
            'update',
            true
        )->will($this->returnSelf());
        $this->_ipn->processIpnRequest();
    }
}
