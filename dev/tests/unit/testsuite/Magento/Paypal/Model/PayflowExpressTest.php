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

namespace Magento\Paypal\Model;

use Magento\Sales\Model\Order\Payment\Transaction;

class PayflowExpressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\PayflowExpress
     */
    protected $_model;

    /**
     * Payflow pro transaction key
     */
    const TRANSPORT_PAYFLOW_TXN_ID = 'Payflow pro transaction key';

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $proFactory = $this->getMockBuilder(
            'Magento\Paypal\Model\ProFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $api = $this->getMock('Magento\Paypal\Model\Api\Nvp', [], [], '', false);
        $paypalPro = $this->getMockBuilder(
            'Magento\Paypal\Model\Pro'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paypalPro->expects($this->any())->method('getApi')->will($this->returnValue($api));

        $proFactory->expects($this->once())->method('create')->will($this->returnValue($paypalPro));

        $this->_model = $objectManager->getObject('Magento\Paypal\Model\PayflowExpress', ['proFactory' => $proFactory]);
    }

    public function testCanRefundCaptureNotExist()
    {
        $paymentInfo = $this->_getPreparedPaymentInfo();

        $paymentInfo->expects($this->once())->method('lookupTransaction')->with('', Transaction::TYPE_CAPTURE)->will(
            $this->returnValue(false)
        );
        $this->assertFalse($this->_model->canRefund());
    }

    public function testCanRefundCaptureExistNoAdditionalInfo()
    {
        $paymentInfo = $this->_getPreparedPaymentInfo();
        $captureTransaction = $this->_getCaptureTransaction();
        $captureTransaction->expects($this->once())->method('getAdditionalInformation')->with(
            Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID
        )->will($this->returnValue(null));
        $paymentInfo->expects($this->once())->method('lookupTransaction')->with('', Transaction::TYPE_CAPTURE)->will(
            $this->returnValue($captureTransaction)
        );
        $this->assertFalse($this->_model->canRefund());
    }

    public function testCanRefundCaptureExistValid()
    {
        $paymentInfo = $this->_getPreparedPaymentInfo();
        $captureTransaction = $this->_getCaptureTransaction();
        $captureTransaction->expects($this->once())->method('getAdditionalInformation')->with(
            Payflow\Pro::TRANSPORT_PAYFLOW_TXN_ID
        )->will($this->returnValue(self::TRANSPORT_PAYFLOW_TXN_ID));
        $paymentInfo->expects($this->once())->method('lookupTransaction')->with('', Transaction::TYPE_CAPTURE)->will(
            $this->returnValue($captureTransaction)
        );
        $this->assertTrue($this->_model->canRefund());
    }

    /**
     * Prepares payment info mock and adds it to the model
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getPreparedPaymentInfo()
    {
        $paymentInfo = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $this->_model->setData('info_instance', $paymentInfo);
        return $paymentInfo;
    }

    /**
     * Prepares capture transaction
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCaptureTransaction()
    {
        return $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment\Transaction'
        )->disableOriginalConstructor()->setMethods([])->getMock();
    }
}
