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

class AddFormExcludedAttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\RecurringPayment\Model\Observer\AddFormExcludedAttribute
     */
    protected $_testModel;


    /**
     * @var \Magento\Framework\Event
     */
    protected $_event;

    /**
     * @var \Magento\RecurringPayment\Model\Payment
     */
    protected $_payment;

    protected function setUp()
    {
        $this->_observer = $this->getMock('Magento\Framework\Event\Observer', array(), array(), '', false);

        $this->_testModel = new \Magento\RecurringPayment\Model\Observer\AddFormExcludedAttribute();

        $this->_event = $this->getMock(
            'Magento\Framework\Event',
            array('getProductElement', 'getProduct', 'getResult', 'getBuyRequest', 'getQuote', 'getApi', 'getObject'),
            array(),
            '',
            false
        );

        $this->_observer->expects($this->any())->method('getEvent')->will($this->returnValue($this->_event));
    }

    public function testExecute()
    {
        $block = $this->getMock(
            'Magento\Backend\Block\Template',
            array('getFormExcludedFieldList', 'setFormExcludedFieldList'),
            array(),
            '',
            false
        );
        $block->expects($this->once())->method('getFormExcludedFieldList')->will($this->returnValue(array('field')));
        $block->expects($this->once())->method('setFormExcludedFieldList')->with(array('field', 'recurring_payment'));

        $this->_event->expects($this->once())->method('getObject')->will($this->returnValue($block));
        $this->_testModel->execute($this->_observer);
    }
} 
