<?php
/**
 * Test class for Mage_Webapi_Model_Resource_Acl_Rule
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Resource_Acl_RuleTest extends Mage_Webapi_Model_Resource_Acl_TestAbstract
{
    /**
     * Create resource model
     *
     * @param Varien_Db_Select $selectMock
     * @return Mage_Webapi_Model_Resource_Acl_Rule
     */
    protected function _createModel($selectMock = null)
    {
        $this->_resource = $this->getMockBuilder('Mage_Core_Model_Resource')
            ->disableOriginalConstructor()
            ->setMethods(array('getConnection', 'getTableName'))
            ->getMock();

        $this->_resource->expects($this->any())
            ->method('getTableName')
            ->withAnyParameters()
            ->will($this->returnArgument(0));

        $this->_adapter = $this->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array('select', 'fetchCol', 'fetchAll',
                'beginTransaction', 'commit', 'rollback', 'insertArray', 'delete'))
            ->getMock();

        $this->_adapter->expects($this->any())
            ->method('fetchCol')
            ->withAnyParameters()
            ->will($this->returnValue(array(1)));

        $this->_adapter->expects($this->any())
            ->method('fetchAll')
            ->withAnyParameters()
            ->will($this->returnValue(array(array('key' => 'value'))));

        if (!$selectMock) {
            $selectMock = new Varien_Db_Select(
                $this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false));
        }

        $this->_adapter->expects($this->any())
            ->method('select')
            ->withAnyParameters()
            ->will($this->returnValue($selectMock));

        $this->_adapter->expects($this->any())
            ->method('beginTransaction')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->_resource->expects($this->any())
            ->method('getConnection')
            ->withAnyParameters()
            ->will($this->returnValue($this->_adapter));

        return $this->_helper->getModel('Mage_Webapi_Model_Resource_Acl_Rule', array(
            'resource' => $this->_resource,
            'helper' => $this->_helperData
        ));
    }

    /**
     * Test constructor
     */
    public function testConstructor()
    {
        $model = $this->_createModel();

        $this->assertAttributeEquals('webapi_rule', '_mainTable', $model);
        $this->assertAttributeEquals('rule_id', '_idFieldName', $model);
    }

    /**
     * Test getRuleList()
     */
    public function testGetRuleList()
    {
        $selectMock = $this->getMockBuilder('Varien_Db_Select')
            ->setConstructorArgs(array($this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false)))
            ->setMethods(array('from'))
            ->getMock();

        $selectMock->expects($this->once())
            ->method('from')
            ->with('webapi_rule', array('resource_id', 'role_id'))
            ->will($this->returnSelf());

        $model = $this->_createModel($selectMock);
        $result = $model->getRuleList();
        $this->assertEquals(array(array('key' => 'value')), $result);
    }

    /**
     * Test getResourceIdsByRole()
     */
    public function testGetResourceIdsByRole()
    {
        $selectMock = $this->getMockBuilder('Varien_Db_Select')
            ->setConstructorArgs(array($this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false)))
            ->setMethods(array('from', 'where'))
            ->getMock();

        $selectMock->expects($this->once())
            ->method('from')
            ->with('webapi_rule', array('resource_id'))
            ->will($this->returnSelf());

        $selectMock->expects($this->once())
            ->method('where')
            ->with('role_id = ?', 1)
            ->will($this->returnSelf());

        $model = $this->_createModel($selectMock);
        $result = $model->getResourceIdsByRole(1);
        $this->assertEquals(array(1), $result);
    }

    /**
     * Test saveResources()
     */
    public function testSaveResources()
    {
        // init rule resource
        $ruleResource = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_Rule')
            ->disableOriginalConstructor()
            ->setMethods(array('saveResources', 'getIdFieldName', 'getReadConnection', 'getResources'))
            ->getMock();

        $ruleResource->expects($this->any())
            ->method('getIdFieldName')
            ->withAnyParameters()
            ->will($this->returnValue('id'));

        $ruleResource->expects($this->any())
            ->method('getReadConnection')
            ->withAnyParameters()
            ->will($this->returnValue($this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false)));

        // init rule
        $rule = $this->getMockBuilder('Mage_Webapi_Model_Acl_Rule')
            ->setConstructorArgs(array(
                'eventDispatcher' => $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false),
                'cacheManager' => $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false),
                'resource' => $ruleResource
            ))
            ->setMethods(array('getResources'))
            ->getMock();

        $rule->expects($this->once())
            ->method('getResources')
            ->withAnyParameters()
            ->will($this->returnValue(array('ResourceName')));

        $model = $this->_createModel();

        // init adapter
        $this->_adapter->expects($this->any())
            ->method('delete')
            ->withAnyParameters()
            ->will($this->returnValue(array()));

        $this->_adapter->expects($this->once())
            ->method('insertArray')
            ->with('webapi_rule', array('role_id', 'resource_id'),
                array(array('role_id' => 1, 'resource_id' => 'ResourceName')))
            ->will($this->returnValue(1));

        $rule->setRoleId(1);
        $model->saveResources($rule);

        // init adapter
        $this->_adapter->expects($this->any())
            ->method('delete')
            ->withAnyParameters()
            ->will($this->throwException(new Zend_Db_Adapter_Exception('DB Exception')));

        $this->setExpectedException('Zend_Db_Adapter_Exception', 'DB Exception');
        $model->saveResources($rule);
    }
}
