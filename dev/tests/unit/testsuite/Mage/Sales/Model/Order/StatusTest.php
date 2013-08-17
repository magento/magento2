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
 * @package     Mage_Sales
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Model_Order_StatusTest extends PHPUnit_Framework_TestCase
{
    /**
     * Retrieve prepared for test Mage_Sales_Model_Order_Status
     *
     * @param null|PHPUnit_Framework_MockObject_MockObject $resource
     * @param null|PHPUnit_Framework_MockObject_MockObject $eventDispatcher
     * @return Mage_Sales_Model_Order_Status
     */
    protected function _getPreparedModel($resource = null, $eventDispatcher = null)
    {
        if (!$resource) {
            $resource = $this->getMock('Mage_Sales_Model_Resource_Order_Status', array(), array(), '', false);
        }
        if (!$eventDispatcher) {
            $eventDispatcher = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        }
        $helper = new Magento_Test_Helper_ObjectManager($this);
        $model = $helper->getObject('Mage_Sales_Model_Order_Status', array(
            'resource' => $resource,
            'eventDispatcher' => $eventDispatcher
        ));
        return $model;
    }

    public function testUnassignState()
    {
        $state = 'test_state';
        $status = 'test_status';

        $resource = $this->getMock('Mage_Sales_Model_Resource_Order_Status', array(), array(), '', false);
        $resource->expects($this->once())->method('beginTransaction');
        $resource->expects($this->once())->method('unassignState')
            ->with($this->equalTo($status), $this->equalTo($state));
        $resource->expects($this->once())->method('commit');

        $params = array('status' => $status, 'state' => $state);
        $eventDispatcher = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        $eventDispatcher->expects($this->once())->method('dispatch')
            ->with($this->equalTo('sales_order_status_unassign'), $this->equalTo($params));

        $model = $this->_getPreparedModel($resource, $eventDispatcher);
        $model->setStatus($status);
        $this->assertInstanceOf('Mage_Sales_Model_Order_Status', $model->unassignState($state));
    }
}
