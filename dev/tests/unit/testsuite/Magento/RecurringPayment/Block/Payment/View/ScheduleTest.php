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
namespace Magento\RecurringPayment\Block\Payment\View;

/**
 * Test class for \Magento\RecurringPayment\Block\Payment\View\Schedule
 */
class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\RecurringPayment\Block\Payment\View\Schedule
     */
    protected $_block;

    /**
     * @var \Magento\RecurringPayment\Model\Payment
     */
    protected $_payment;

    public function testPrepareLayout()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_payment = $this->getMockBuilder(
            'Magento\RecurringPayment\Model\Payment'
        )->disableOriginalConstructor()->setMethods(
            array('setStore', 'getFieldLabel', 'renderData', '__wakeup')
        )->getMock();
        $this->_payment->expects($this->once())->method('setStore')->will($this->returnValue($this->_payment));

        $registry = $this->getMockBuilder(
            'Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            array('registry')
        )->getMock();
        $registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_recurring_payment'
        )->will(
            $this->returnValue($this->_payment)
        );

        $store = $this->getMockBuilder('Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();

        $storeManager = $this->getMockBuilder(
            'Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->setMethods(
            array('getStore')
        )->getMock();
        $storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->_block = $objectManager->getObject(
            'Magento\RecurringPayment\Block\Payment\View\Schedule',
            array('registry' => $registry, 'storeManager' => $storeManager)
        );

        $layout = $this->getMockBuilder(
            'Magento\Framework\View\Layout'
        )->disableOriginalConstructor()->setMethods(
            array('helper')
        )->getMock();

        $this->_block->setLayout($layout);

        $this->assertNotEmpty($this->_block->getRenderedInfo());
    }
}
