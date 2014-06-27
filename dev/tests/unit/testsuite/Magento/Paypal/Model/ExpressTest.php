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

use Magento\Framework\Object;
use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;

class ExpressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $errorCodes = [
        ApiProcessableException::API_INTERNAL_ERROR,
        ApiProcessableException::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE,
        ApiProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL,
        ApiProcessableException::API_UNABLE_TRANSACTION_COMPLETE,
        ApiProcessableException::API_TRANSACTION_EXPIRED,
        ApiProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED,
        ApiProcessableException::API_COUNTRY_FILTER_DECLINE,
        ApiProcessableException::API_MAXIMUM_AMOUNT_FILTER_DECLINE,
        ApiProcessableException::API_OTHER_FILTER_DECLINE
    ];

    /**
     * @var Express
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pro;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_nvp;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_checkoutSession = $this->getMock(
            'Magento\Checkout\Model\Session',
            ['getPaypalTransactionData', 'setPaypalTransactionData'],
            [],
            '',
            false
        );
        $this->_nvp = $this->getMock(
            'Magento\Paypal\Model\Api\Nvp',
            ['setProcessableErrors', 'setAmount', 'setCurrencyCode', 'setTransactionId', 'callDoAuthorization'],
            [],
            '',
            false
        );
        $this->_pro = $this->getMock(
            'Magento\Paypal\Model\ProFactory',
            ['create', 'setMethod', 'getApi', 'importPaymentInfo', 'resetApi'],
            [],
            '',
            false
        );
        $this->_pro->expects($this->any())->method('create')->will($this->returnSelf());
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testSetApiProcessableErrors()
    {
        $this->_nvp->expects($this->once())->method('setProcessableErrors')->with($this->errorCodes);
        $this->_pro->expects($this->any())->method('getApi')->will($this->returnValue($this->_nvp));
        $this->_model = $this->_helper->getObject(
            'Magento\Paypal\Model\Express',
            ['proFactory' => $this->_pro, 'checkoutSession' => $this->_checkoutSession]
        );
    }

    public function testOrder()
    {
        $this->_nvp->expects($this->any())->method('setProcessableErrors')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('setAmount')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('setCurrencyCode')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('setTransactionId')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('callDoAuthorization')->will($this->returnSelf());
        $this->_pro->expects($this->any())->method('getApi')->will($this->returnValue($this->_nvp));
        $this->_checkoutSession->expects($this->once())->method('getPaypalTransactionData')->will(
            $this->returnValue([])
        );
        $this->_checkoutSession->expects($this->once())->method('setPaypalTransactionData')->with([]);

        $currency = $this->getMock('Magento\Directory\Model\Currency', ['__wakeup', 'formatTxt'], [], '', false);
        $paymentModel = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            ['__wakeup', 'getBaseCurrency', 'getOrder', 'getIsTransactionPending', 'addStatusHistoryComment'],
            [],
            '',
            false
        );
        $paymentModel->expects($this->any())->method('getOrder')->will($this->returnSelf());
        $paymentModel->expects($this->any())->method('getBaseCurrency')->will($this->returnValue($currency));
        $paymentModel->expects($this->any())->method('getIsTransactionPending')->will($this->returnSelf());
        $this->_model = $this->_helper->getObject(
            'Magento\Paypal\Model\Express',
            ['proFactory' => $this->_pro, 'checkoutSession' => $this->_checkoutSession]
        );
        $this->_model->order($paymentModel, 12.3);
        $this->assertEquals('payment_review', $paymentModel->getState());
    }
}
