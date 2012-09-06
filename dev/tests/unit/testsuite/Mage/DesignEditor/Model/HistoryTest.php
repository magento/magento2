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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_HistoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Mage_DesignEditor_Model_History::getCompactLog
     */
    public function testGetCompactLog()
    {
        $methods = array('_getManagerModel');
        /** @var $historyMock Mage_DesignEditor_Model_History */
        $historyMock = $this->getMock('Mage_DesignEditor_Model_History', $methods, array(), '', false);

        $methods = array('getHistoryLog', 'addChange');
        /** @var $managerMock Mage_DesignEditor_Model_History_Manager */
        $managerMock = $this->getMock('Mage_DesignEditor_Model_History_Manager', $methods, array(), '', false);

        $historyMock->expects($this->exactly(2))
            ->method('_getManagerModel')
            ->will($this->returnValue($managerMock));

        $managerMock->expects($this->exactly(4))
            ->method('addChange')
            ->will($this->returnValue($managerMock));

        $managerMock->expects($this->once())
            ->method('getHistoryLog')
            ->will($this->returnValue(array()));

        $historyMock->setChangeLog($this->_getChangeLogData())->getCompactLog();
    }

    /**
     * @covers Mage_DesignEditor_Model_History::getCompactLog
     * @expectedException Mage_DesignEditor_Exception
     */
    public function testGetCompactLogWithInvalidData()
    {
        $this->_mockTranslationHelper();

        $methods = array('_getManagerModel');
        /** @var $historyMock Mage_DesignEditor_Model_History */
        $historyMock = $this->getMock('Mage_DesignEditor_Model_History', $methods, array(), '', false);

        $methods = array('addChange');
        /** @var $managerMock Mage_DesignEditor_Model_History_Manager */
        $managerMock = $this->getMock('Mage_DesignEditor_Model_History_Manager', $methods, array(), '', false);

        $historyMock->expects($this->exactly(1))
            ->method('_getManagerModel')
            ->will($this->returnValue($managerMock));

        $managerMock->expects($this->exactly(1))
            ->method('addChange')
            ->will($this->returnValue($managerMock));

        $historyMock->setChangeLog($this->_getInvalidChangeLogData())->getCompactLog();
    }

    /**
     * @covers Mage_DesignEditor_Model_History::getCompactXml
     */
    public function testGetCompactXml()
    {
        $methods = array('_getManagerModel');
        /** @var $historyMock Mage_DesignEditor_Model_History */
        $historyMock = $this->getMock('Mage_DesignEditor_Model_History', $methods, array(), '', false);

        $methods = array('getXml', 'addChange');
        /** @var $managerMock Mage_DesignEditor_Model_History_Manager */
        $managerMock = $this->getMock('Mage_DesignEditor_Model_History_Manager', $methods, array(), '', false);

        $historyMock->expects($this->exactly(2))
            ->method('_getManagerModel')
            ->will($this->returnValue($managerMock));

        $managerMock->expects($this->exactly(4))
            ->method('addChange')
            ->will($this->returnValue($managerMock));

        $managerMock->expects($this->once())
            ->method('getXml')
            ->will($this->returnValue(array()));

        $historyMock->setChangeLog($this->_getChangeLogData())->getCompactXml();
    }

    /**
     * @covers Mage_DesignEditor_Model_History::getCompactXml
     * @expectedException Mage_DesignEditor_Exception
     */
    public function testGetCompactXmlWithInvalidData()
    {
        $this->_mockTranslationHelper();

        $methods = array('_getManagerModel');
        /** @var $historyMock Mage_DesignEditor_Model_History */
        $historyMock = $this->getMock('Mage_DesignEditor_Model_History', $methods, array(), '', false);

        $methods = array('addChange');
        /** @var $managerMock Mage_DesignEditor_Model_History_Manager */
        $managerMock = $this->getMock('Mage_DesignEditor_Model_History_Manager', $methods, array(), '', false);

        $historyMock->expects($this->exactly(1))
            ->method('_getManagerModel')
            ->will($this->returnValue($managerMock));

        $managerMock->expects($this->exactly(1))
            ->method('addChange')
            ->will($this->returnValue($managerMock));

        $historyMock->setChangeLog($this->_getInvalidChangeLogData())->getCompactXml();
    }

    protected function _getChangeLogData()
    {
        return array(
            array(
                'handle'       => 'checkout_cart_index',
                'change_type'  => 'layout',
                'element_name' => 'checkout.cart',
                'action_name'  => 'move',
                'action_data'  => array(
                    'destination_container' => 'content',
                    'after'                 => '-',
                ),
            ),
            array(
                'handle'       => 'checkout_cart_index',
                'change_type'  => 'layout',
                'element_name' => 'checkout.cart',
                'action_name'  => 'remove',
                'action_data'  => array(),
            ),
            array(
                'handle'       => 'customer_account',
                'change_type'  => 'layout',
                'element_name' => 'customer_account_navigation',
                'action_name'  => 'move',
                'action_data'  => array(
                    'destination_container' => 'content',
                    'after'                 => '-',
                    'as'                    => 'customer_account_navigation_alias',
                ),
            ),
            array(
                'handle'       => 'customer_account',
                'change_type'  => 'layout',
                'element_name' => 'customer_account_navigation',
                'action_name'  => 'move',
                'action_data'  => array(
                    'destination_container' => 'top.menu',
                    'after'                 => '-',
                    'as'                    => 'customer_account_navigation_alias',
                ),
            ),
        );
    }

    protected function _getInvalidChangeLogData()
    {
        return array(
            array(
                'handle'       => 'checkout_cart_index',
                'change_type'  => 'layout',
                'element_name' => 'checkout.cart',
                'action_name'  => 'move',
                'action_data'  => array(
                    'destination_container' => 'content',
                    'after'                 => '-',
                ),
            ),
            array(
                'handle'       => '',
                'change_type'  => '',
                'element_name' => '',
                'action_name'  => '',
            ),
        );
    }

    /**
     * Add/remove mock for translation helper
     *
     * @param bool $add
     * @return void
     */
    protected function _mockTranslationHelper($add = true)
    {
        Mage::unregister('_helper/Mage_DesignEditor_Helper_Data');
        if ($add) {
            $helper = $this->getMock('stdClass', array('__'));
            $helper->expects($this->any())->method('__')->will($this->returnArgument(0));
            Mage::register('_helper/Mage_DesignEditor_Helper_Data', $helper);
        }
    }
}

class Mage_DesignEditor_Model_HistoryTest_Exception extends Exception
{
}
