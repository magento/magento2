<?php
/**
 *
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
namespace Magento\RecurringPayment\Model\Observer;

class PrepareProductRecurringPaymentOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\RecurringPayment\Model\Observer\PrepareProductRecurringPaymentOptions
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

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_testModel = $helper->getObject(
            'Magento\RecurringPayment\Model\Observer\PrepareProductRecurringPaymentOptions',
            array(
                'recurringPaymentFactory' => $this->_recurringPaymentFactory,
                'fields' => $this->_fieldsBlock,
                'paymentFactory' => $this->_paymentFactory
            )
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

    public function testExecute()
    {
        $payment = $this->getMock(
            'Magento\Framework\Object',
            array(
                'setStory',
                'importBuyRequest',
                'importProduct',
                'exportStartDatetime',
                'exportScheduleInfo',
                'getFieldLabel'
            ),
            array(),
            '',
            false
        );
        $payment->expects($this->once())->method('exportStartDatetime')->will($this->returnValue('date'));
        $payment->expects($this->any())->method('setStore')->will($this->returnValue($payment));
        $payment->expects($this->once())->method('importBuyRequest')->will($this->returnValue($payment));
        $payment->expects(
            $this->once()
        )->method(
                'exportScheduleInfo'
            )->will(
                $this->returnValue(
                    array(new \Magento\Framework\Object(array('title' => 'Title', 'schedule' => 'schedule')))
                )
            );

        $this->_fieldsBlock->expects($this->once())->method('getFieldLabel')->will($this->returnValue('Field Label'));

        $this->_recurringPaymentFactory->expects($this->once())->method('create')->will($this->returnValue($payment));

        $product = $this->getMock(
            'Magento\Framework\Object',
            array('getIsRecurring', 'addCustomOption'),
            array(),
            '',
            false
        );
        $product->expects($this->once())->method('getIsRecurring')->will($this->returnValue(true));

        $infoOptions = array(
            array('label' => 'Field Label', 'value' => 'date'),
            array('label' => 'Title', 'value' => 'schedule')
        );

        $product->expects(
            $this->at(2)
        )->method(
                'addCustomOption'
            )->with(
                'additional_options',
                serialize($infoOptions)
            );

        $this->_event->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $this->_testModel->execute($this->_observer);
    }
}
