<?php
/**
 * Test class for \Magento\Webapi\Model\Resource\Acl\Rule
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
namespace Magento\Webapi\Model\Resource\Acl;

class RuleTest extends \Magento\Webapi\Model\Resource\Acl\AbstractTest
{
    /**
     * Create resource model.
     *
     * @param \Magento\DB\Select $selectMock
     * @return \Magento\Webapi\Model\Resource\Acl\Rule
     */
    protected function _createModel($selectMock = null)
    {
        $this->_resource = $this->getMockBuilder('Magento\Core\Model\Resource')
            ->disableOriginalConstructor()
            ->setMethods(array('getConnection', 'getTableName'))
            ->getMock();

        $this->_resource->expects($this->any())
            ->method('getTableName')
            ->withAnyParameters()
            ->will($this->returnArgument(0));

        $this->_adapter = $this->getMockBuilder('Magento\DB\Adapter\Pdo\Mysql')
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
            $selectMock = new \Magento\DB\Select(
                $this->getMock('Magento\DB\Adapter\Pdo\Mysql', array(), array(), '', false));
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

        return $this->_helper->getObject('Magento\Webapi\Model\Resource\Acl\Rule', array(
            'resource' => $this->_resource,
        ));
    }

    /**
     * Test constructor.
     */
    public function testConstructor()
    {
        $model = $this->_createModel();

        $this->assertAttributeEquals('webapi_rule', '_mainTable', $model);
        $this->assertAttributeEquals('rule_id', '_idFieldName', $model);
    }

    /**
     * Test getRuleList().
     */
    public function testGetRuleList()
    {
        $selectMock = $this->getMockBuilder('Magento\DB\Select')
            ->setConstructorArgs(array($this->getMock('Magento\DB\Adapter\Pdo\Mysql', array(), array(), '', false)))
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
     * Test getResourceIdsByRole().
     */
    public function testGetResourceIdsByRole()
    {
        $selectMock = $this->getMockBuilder('Magento\DB\Select')
            ->setConstructorArgs(array($this->getMock('Magento\DB\Adapter\Pdo\Mysql', array(), array(), '', false)))
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
     * Test saveResources().
     */
    public function testSaveResources()
    {
        // Init rule resource.
        $ruleResource = $this->getMockBuilder('Magento\Webapi\Model\Resource\Acl\Rule')
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
            ->will($this->returnValue($this->getMock('Magento\DB\Adapter\Pdo\Mysql', array(), array(), '', false)));

        // Init rule.
        $rule = $this->getMockBuilder('Magento\Webapi\Model\Acl\Rule')
            ->setConstructorArgs(array(
                'context' => $this->getMock('Magento\Core\Model\Context', array(), array(), '', false),
                'coreRegistry' => $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false),
                'resource' => $ruleResource
            ))
            ->setMethods(array('getResources'))
            ->getMock();

        $rule->expects($this->once())
            ->method('getResources')
            ->withAnyParameters()
            ->will($this->returnValue(array('ResourceName')));

        $model = $this->_createModel();

        // Init adapter.
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

        // Init adapter.
        $this->_adapter->expects($this->any())
            ->method('delete')
            ->withAnyParameters()
            ->will($this->throwException(new \Zend_Db_Adapter_Exception('DB \Exception')));

        $this->setExpectedException('Zend_Db_Adapter_Exception', 'DB \Exception');
        $model->saveResources($rule);
    }
}
