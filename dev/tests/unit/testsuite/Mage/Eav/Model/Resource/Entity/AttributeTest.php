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
 * @package     Mage_Eav
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Eav_Model_Resource_Entity_AttributeTest extends Magento_Test_TestCase_ObjectManagerAbstract
{
    /**
     * @covers Mage_Eav_Model_Resource_Entity_Attribute::_saveOption
     */
    public function testSaveOptionSystemAttribute()
    {
        /** @var $adapter PHPUnit_Framework_MockObject_MockObject */
        /** @var $resourceModel Mage_Eav_Model_Resource_Entity_Attribute */
        list($adapter, $resourceModel) = $this->_prepareResourceModel();

        $attributeData = array(
            'attribute_id' => '123',
            'entity_type_id' => 4,
            'attribute_code' => 'status',
            'backend_model' => null,
            'backend_type' => 'int',
            'frontend_input' => 'select',
            'frontend_label' => 'Status',
            'frontend_class' => null,
            'source_model' => 'Mage_Catalog_Model_Product_Status',
            'is_required' => 1,
            'is_user_defined' => 0,
            'is_unique' => 0,
        );

        /** @var $model Mage_Core_Model_Abstract */
        $arguments = $this->_getConstructArguments(self::MODEL_ENTITY);
        $arguments['data'] = $attributeData;
        $model = $this->getMock('Mage_Core_Model_Abstract', null, $arguments);
        $model->setDefault(array('2'));
        $model->setOption(array('delete' => array(1 => '', 2 => '')));

        $adapter->expects($this->once())
            ->method('insert')
            ->will($this->returnValueMap(array(
            array('eav_attribute', $attributeData, 1),
        )));

        //this line causes crash on windows environment
        //$adapter->expects($this->never())->method('update');
        $adapter->expects($this->never())->method('delete');

        $adapter->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValueMap(array(
                array(
                    'SELECT `eav_attribute`.* FROM `eav_attribute` '
                        . 'WHERE (attribute_code="status") AND (entity_type_id="4")',
                    $attributeData
                ),
            )));

        $resourceModel->save($model);
    }

    /**
     * @covers Mage_Eav_Model_Resource_Entity_Attribute::_saveOption
     */
    public function testSaveOptionNewUserDefinedAttribute()
    {
        /** @var $adapter PHPUnit_Framework_MockObject_MockObject */
        /** @var $resourceModel Mage_Eav_Model_Resource_Entity_Attribute */
        list($adapter, $resourceModel) = $this->_prepareResourceModel();

        $attributeData = array(
            'entity_type_id' => 4,
            'attribute_code' => 'a_dropdown',
            'backend_model' => null,
            'backend_type' => 'int',
            'frontend_input' => 'select',
            'frontend_label' => 'A Dropdown',
            'frontend_class' => null,
            'source_model' => 'Mage_Eav_Model_Entity_Attribute_Source_Table',
            'is_required' => 0,
            'is_user_defined' => 1,
            'is_unique' => 0,
        );

        /** @var $model Mage_Core_Model_Abstract */
        $arguments = $this->_getConstructArguments(self::MODEL_ENTITY);
        $arguments['data'] = $attributeData;
        $model = $this->getMock('Mage_Core_Model_Abstract', null, $arguments);
        $model->setOption(array('value' => array('option_1' => array('Backend Label', 'Frontend Label'))));

        $adapter->expects($this->any())
            ->method('lastInsertId')
            ->will($this->returnValueMap(array(
                array('eav_attribute', 123),
                array('eav_attribute_option_value', 321),
            )));
        $adapter->expects($this->once())
            ->method('update')
            ->will($this->returnValueMap(array(
                array('eav_attribute', array ('default_value' => ''), array ('attribute_id = ?' => 123), 1),
            )));
        $adapter->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValueMap(array(
                array(
                    'SELECT `eav_attribute`.* FROM `eav_attribute` '
                        . 'WHERE (attribute_code="a_dropdown") AND (entity_type_id="4")',
                    false
                ),
            )));
        $adapter->expects($this->once())
            ->method('delete')
            ->will($this->returnValueMap(array(
                array('eav_attribute_option_value', array('option_id = ?' => ''), 0),
            )));
        $adapter->expects($this->exactly(4))
            ->method('insert')
            ->will($this->returnValueMap(array(
                array('eav_attribute', $attributeData, 1),
                array('eav_attribute_option', array('attribute_id' => 123, 'sort_order' => 0), 1),
                array(
                    'eav_attribute_option_value',
                    array('option_id' => 123, 'store_id' => 0, 'value' => 'Backend Label'),
                    1
                ),
                array(
                    'eav_attribute_option_value',
                    array('option_id' => 123, 'store_id' => 1, 'value' => 'Frontend Label'),
                    1
                ),
            )));

        $resourceModel->save($model);
    }

    /**
     * @covers Mage_Eav_Model_Resource_Entity_Attribute::_saveOption
     */
    public function testSaveOptionNoValue()
    {
        /** @var $adapter PHPUnit_Framework_MockObject_MockObject */
        /** @var $resourceModel Mage_Eav_Model_Resource_Entity_Attribute */
        list($adapter, $resourceModel) = $this->_prepareResourceModel();

        /** @var $model Mage_Core_Model_Abstract */
        $arguments = $this->_getConstructArguments(self::MODEL_ENTITY);
        $model = $this->getMock('Mage_Core_Model_Abstract', null, $arguments);
        $model->setOption('not-an-array');

        $adapter->expects($this->once())->method('insert')->with('eav_attribute');
        $adapter->expects($this->never())->method('delete');
        $adapter->expects($this->never())->method('update');

        $resourceModel->save($model);
    }

    /**
     * Retrieve resource model mock instance and its adapter
     *
     * @return array
     */
    protected function _prepareResourceModel()
    {
        $adapter = $this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(
            '_connect', 'delete', 'describeTable', 'fetchRow', 'insert', 'lastInsertId', 'quote', 'update',
        ), array(), '', false);
        $adapter->expects($this->any())
            ->method('describeTable')
            ->with('eav_attribute')
            ->will($this->returnValue($this->_describeEavAttribute()));
        $adapter->expects($this->any())
            ->method('quote')
            ->will($this->returnValueMap(array(
                array(123, 123),
                array('4', '"4"'),
                array('a_dropdown', '"a_dropdown"'),
                array('status', '"status"'),
            )));

        $application = $this->getMock('Mage_Core_Model_App', array('getStores'), array(), '', false);
        $application->expects($this->any())
            ->method('getStores')
            ->with(true)
            ->will($this->returnValue(array(
                new Varien_Object(array('id' => 0)),
                new Varien_Object(array('id' => 1)),
            )));

        /** @var $resource Mage_Core_Model_Resource */
        $resource = $this->getMock('Mage_Core_Model_Resource', array('getTableName', 'getConnection'));
        $resource->expects($this->any())
            ->method('getTableName')
            ->will($this->returnArgument(0));
        $resource->expects($this->any())
            ->method('getConnection')
            ->with()
            ->will($this->returnValue($adapter));

        $arguments = array(
            'resource'  => $resource,
            'arguments' => array(
                'application' => $application,
                'helper'      => $this->getMock('Mage_Eav_Helper_Data'),
            )
        );
        $resourceModel = $this->getMock(
            'Mage_Eav_Model_Resource_Entity_Attribute',
            array('getAdditionalAttributeTable'), // Mage::getResourceSingleton dependency
            $arguments
        );

        return array($adapter, $resourceModel);
    }

    /**
     * Retrieve eav_attribute table structure
     *
     * @return array
     */
    protected function _describeEavAttribute()
    {
        return require __DIR__ . '/../../../_files/describe_table_eav_attribute.php';
    }
}
