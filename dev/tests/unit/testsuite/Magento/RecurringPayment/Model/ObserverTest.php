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

namespace Magento\RecurringPayment\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Event\Observer
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
     * @var \Magento\Event
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
        $this->_observer = $this->getMock('Magento\Event\Observer', [], [], '', false);
        $this->_fieldsBlock = $this->getMock(
            '\Magento\RecurringPayment\Block\Fields', ['getFieldLabel'], [], '', false
        );
        $this->_recurringPaymentFactory = $this->getMock(
            '\Magento\RecurringPayment\Model\RecurringPaymentFactory', ['create'], [], '', false
        );
        $this->_paymentFactory = $this->getMock(
            '\Magento\RecurringPayment\Model\PaymentFactory', ['create', 'importProduct'], [], '', false
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_testModel = $helper->getObject('Magento\RecurringPayment\Model\Observer', [
            'recurringPaymentFactory' => $this->_recurringPaymentFactory,
            'fields' => $this->_fieldsBlock,
            'paymentFactory' => $this->_paymentFactory
        ]);

        $this->_event = $this->getMock(
            'Magento\Event', [
                'getProductElement', 'getProduct', 'getResult', 'getBuyRequest', 'getQuote', 'getApi', 'getObject'
            ], [], '', false
        );

        $this->_observer->expects($this->any())->method('getEvent')->will($this->returnValue($this->_event));
        $this->_payment = $this->getMock('Magento\RecurringPayment\Model\Payment', [
            '__sleep', '__wakeup', 'isValid', 'importQuote', 'importQuoteItem', 'submit', 'getId', 'setMethodCode'
        ], [], '', false);
    }

    public function testPrepareProductRecurringPaymentOptions()
    {
        $payment = $this->getMock(
            'Magento\Object',
            [
                'setStory',
                'importBuyRequest',
                'importProduct',
                'exportStartDatetime',
                'exportScheduleInfo',
                'getFieldLabel'
            ],
            [],
            '',
            false
        );
        $payment->expects($this->once())->method('exportStartDatetime')->will($this->returnValue('date'));
        $payment->expects($this->any())->method('setStore')->will($this->returnValue($payment));
        $payment->expects($this->once())->method('importBuyRequest')->will($this->returnValue($payment));
        $payment->expects($this->once())->method('exportScheduleInfo')
            ->will($this->returnValue([new \Magento\Object(['title' => 'Title', 'schedule' => 'schedule'])]));

        $this->_fieldsBlock->expects($this->once())->method('getFieldLabel')->will($this->returnValue('Field Label'));

        $this->_recurringPaymentFactory->expects($this->once())->method('create')->will($this->returnValue($payment));

        $product = $this->getMock('Magento\Object', ['getIsRecurring', 'addCustomOption'], [], '', false);
        $product->expects($this->once())->method('getIsRecurring')->will($this->returnValue(true));

        $infoOptions = [
            ['label' => 'Field Label', 'value' => 'date'],
            ['label' => 'Title', 'value' => 'schedule']
        ];

        $product->expects($this->at(2))->method('addCustomOption')->with(
            'additional_options',
            serialize($infoOptions)
        );

        $this->_event->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $this->_testModel->prepareProductRecurringPaymentOptions($this->_observer);
    }

    public function testAddFormExcludedAttribute()
    {
        $block = $this->getMock('Magento\Backend\Block\Template', [
            'getFormExcludedFieldList', 'setFormExcludedFieldList'
        ], [], '', false);
        $block->expects($this->once())->method('getFormExcludedFieldList')->will($this->returnValue(['field']));
        $block->expects($this->once())->method('setFormExcludedFieldList')->with(['field', 'recurring_payment']);

        $this->_event->expects($this->once())->method('getObject')->will($this->returnValue($block));
        $this->_testModel->addFormExcludedAttribute($this->_observer);
    }
}
