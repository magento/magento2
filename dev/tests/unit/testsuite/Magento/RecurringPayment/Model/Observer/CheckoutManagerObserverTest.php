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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\RecurringPayment\Model\Observer;

class CheckoutManagerObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\RecurringPayment\Model\Observer
     */
    protected $_testModel;

    /**
     * @var \Magento\RecurringPayment\Block\Fields
     */
    protected $_fieldsBlock;

    /**
     * @var \Magento\RecurringPayment\Model\RecurringPaymentFactory
     */
    protected $_recurringPaymentFactory;

    /**
     * @var \Magento\Framework\Event
     */
    protected $_event;

    /**
     * @var \Magento\RecurringPayment\Model\PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @var \Magento\RecurringPayment\Model\Payment
     */
    protected $_payment;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    protected $_quote;

    protected function setUp()
    {
        $this->_observer = $this->getMock('Magento\Framework\Event\Observer', array(), array(), '', false);
        $this->_fieldsBlock = $this->getMock(
            '\Magento\RecurringPayment\Block\Fields',
            array('getFieldLabel'),
            array(),
            '',
            false
        );
        $this->_recurringPaymentFactory = $this->getMock(
            '\Magento\RecurringPayment\Model\RecurringPaymentFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_paymentFactory = $this->getMock(
            '\Magento\RecurringPayment\Model\PaymentFactory',
            array('create', 'importProduct'),
            array(),
            '',
            false
        );
        $this->_checkoutSession = $this->getMock(
            '\Magento\Checkout\Model\Session',
            array('setLastRecurringPaymentIds'),
            array(),
            '',
            false
        );
        $this->_quote = $this->getMock(
            '\Magento\RecurringPayment\Model\QuoteImporter',
            array('import'),
            array(),
            '',
            false
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_testModel = $helper->getObject(
            'Magento\RecurringPayment\Model\Observer\CheckoutManagerObserver',
            array('checkoutSession' => $this->_checkoutSession, 'quoteImporter' => $this->_quote)
        );

        $this->_event = $this->getMock(
            'Magento\Framework\Event',
            array('getProductElement', 'getProduct', 'getResult', 'getBuyRequest', 'getQuote', 'getApi', 'getObject'),
            array(),
            '',
            false
        );

        $this->_observer->expects($this->any())->method('getEvent')->will($this->returnValue($this->_event));
        $this->_payment = $this->getMock(
            'Magento\RecurringPayment\Model\Payment',
            array(
                '__sleep',
                '__wakeup',
                'isValid',
                'importQuote',
                'importQuoteItem',
                'submit',
                'getId',
                'setMethodCode'
            ),
            array(),
            '',
            false
        );
    }

    public function testSubmitRecurringPayments()
    {
        $this->_prepareRecurringPayments();
        $this->_quote->expects($this->once())->method('import')->will($this->returnValue(array($this->_payment)));

        $this->_payment->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->_payment->expects($this->once())->method('submit');

        $this->_testModel->submitRecurringPayments($this->_observer);
    }

    public function testAddRecurringPaymentIdsToSession()
    {
        $this->_prepareRecurringPayments();
        $this->_quote->expects($this->once())->method('import')->will($this->returnValue(array($this->_payment)));
        $this->_payment->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->_payment->expects($this->once())->method('submit');

        $this->_testModel->submitRecurringPayments($this->_observer);

        $this->_testModel->addRecurringPaymentIdsToSession();
    }

    protected function _prepareRecurringPayments()
    {
        $product = $this->getMock(
            'Magento\RecurringPayment\Model\Payment',
            array('getIsRecurring', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );
        $product->expects($this->any())->method('getIsRecurring')->will($this->returnValue(true));

        $this->_payment = $this->getMock(
            'Magento\RecurringPayment\Model\Payment',
            array(
                '__sleep',
                '__wakeup',
                'isValid',
                'importQuote',
                'importQuoteItem',
                'submit',
                'getId',
                'setMethodCode'
            ),
            array(),
            '',
            false
        );

        $quote = $this->getMock(
            'Magento\Sales\Model\Quote',
            array('getTotalsCollectedFlag', '__sleep', '__wakeup', 'getAllVisibleItems'),
            array(),
            '',
            false
        );

        $this->_event->expects($this->any())->method('getQuote')->will($this->returnValue($quote));
    }
}
