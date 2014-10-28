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
namespace Magento\Eav\Model\Resource\Entity;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Eav\Model\Resource\Entity\Attribute::_saveOption
     */
    public function testSaveOptionSystemAttribute()
    {
        /** @var $adapter \PHPUnit_Framework_MockObject_MockObject */
        /** @var $resourceModel \Magento\Eav\Model\Resource\Entity\Attribute */
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
            'source_model' => 'Magento\Catalog\Model\Product\Attribute\Source\Status',
            'is_required' => 1,
            'is_user_defined' => 0,
            'is_unique' => 0
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $model \Magento\Framework\Model\AbstractModel */
        $arguments = $objectManagerHelper->getConstructArguments('Magento\Framework\Model\AbstractModel');
        $arguments['data'] = $attributeData;
        $model = $this->getMock('Magento\Framework\Model\AbstractModel', null, $arguments);
        $model->setDefault(array('2'));
        $model->setOption(array('delete' => array(1 => '', 2 => '')));

        $adapter->expects(
            $this->once()
        )->method(
            'insert'
        )->will(
            $this->returnValueMap(array(array('eav_attribute', $attributeData, 1)))
        );

        $adapter->expects(
            $this->once()
        )->method(
            'fetchRow'
        )->will(
            $this->returnValueMap(
                array(
                    array(
                        'SELECT `eav_attribute`.* FROM `eav_attribute` ' .
                        'WHERE (attribute_code="status") AND (entity_type_id="4")',
                        $attributeData
                    )
                )
            )
        );
        $adapter->expects(
            $this->once()
        )->method(
            'update'
        )->with(
            'eav_attribute',
            array('default_value' => 2),
            array('attribute_id = ?' => null)
        );
        $adapter->expects($this->never())->method('delete');

        $resourceModel->save($model);
    }

    /**
     * @covers \Magento\Eav\Model\Resource\Entity\Attribute::_saveOption
     */
    public function testSaveOptionNewUserDefinedAttribute()
    {
        /** @var $adapter \PHPUnit_Framework_MockObject_MockObject */
        /** @var $resourceModel \Magento\Eav\Model\Resource\Entity\Attribute */
        list($adapter, $resourceModel) = $this->_prepareResourceModel();

        $attributeData = array(
            'entity_type_id' => 4,
            'attribute_code' => 'a_dropdown',
            'backend_model' => null,
            'backend_type' => 'int',
            'frontend_input' => 'select',
            'frontend_label' => 'A Dropdown',
            'frontend_class' => null,
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'is_required' => 0,
            'is_user_defined' => 1,
            'is_unique' => 0
        );


        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $model \Magento\Framework\Model\AbstractModel */
        $arguments = $objectManagerHelper->getConstructArguments('Magento\Framework\Model\AbstractModel');
        $arguments['data'] = $attributeData;
        $model = $this->getMock('Magento\Framework\Model\AbstractModel', null, $arguments);
        $model->setOption(array('value' => array('option_1' => array('Backend Label', 'Frontend Label'))));

        $adapter->expects(
            $this->any()
        )->method(
            'lastInsertId'
        )->will(
            $this->returnValueMap(array(array('eav_attribute', 123), array('eav_attribute_option_value', 321)))
        );
        $adapter->expects(
            $this->once()
        )->method(
            'update'
        )->will(
            $this->returnValueMap(
                array(array('eav_attribute', array('default_value' => ''), array('attribute_id = ?' => 123), 1))
            )
        );
        $adapter->expects(
            $this->once()
        )->method(
            'fetchRow'
        )->will(
            $this->returnValueMap(
                array(
                    array(
                        'SELECT `eav_attribute`.* FROM `eav_attribute` ' .
                        'WHERE (attribute_code="a_dropdown") AND (entity_type_id="4")',
                        false
                    )
                )
            )
        );
        $adapter->expects(
            $this->once()
        )->method(
            'delete'
        )->will(
            $this->returnValueMap(array(array('eav_attribute_option_value', array('option_id = ?' => ''), 0)))
        );
        $adapter->expects(
            $this->exactly(4)
        )->method(
            'insert'
        )->will(
            $this->returnValueMap(
                array(
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
                    )
                )
            )
        );

        $resourceModel->save($model);
    }

    /**
     * @covers \Magento\Eav\Model\Resource\Entity\Attribute::_saveOption
     */
    public function testSaveOptionNoValue()
    {
        /** @var $adapter \PHPUnit_Framework_MockObject_MockObject */
        /** @var $resourceModel \Magento\Eav\Model\Resource\Entity\Attribute */
        list($adapter, $resourceModel) = $this->_prepareResourceModel();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $model \Magento\Framework\Model\AbstractModel */
        $arguments = $objectManagerHelper->getConstructArguments('Magento\Framework\Model\AbstractModel');
        $model = $this->getMock('Magento\Framework\Model\AbstractModel', null, $arguments);
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
        $adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('_connect', 'delete', 'describeTable', 'fetchRow', 'insert', 'lastInsertId', 'quote', 'update'),
            array(),
            '',
            false
        );
        $adapter->expects(
            $this->any()
        )->method(
            'describeTable'
        )->with(
            'eav_attribute'
        )->will(
            $this->returnValue($this->_describeEavAttribute())
        );
        $adapter->expects(
            $this->any()
        )->method(
            'quote'
        )->will(
            $this->returnValueMap(
                array(
                    array(123, 123),
                    array('4', '"4"'),
                    array('a_dropdown', '"a_dropdown"'),
                    array('status', '"status"')
                )
            )
        );

        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', array('getStores'), array(), '', false);
        $storeManager->expects(
            $this->any()
        )->method(
            'getStores'
        )->with(
            true
        )->will(
            $this->returnValue(array(
                new \Magento\Framework\Object(array('id' => 0)),
                new \Magento\Framework\Object(array('id' => 1)))
            )
        );

        /** @var $resource \Magento\Framework\App\Resource */
        $resource = $this->getMock(
            'Magento\Framework\App\Resource',
            array('getTableName', 'getConnection'),
            array(),
            '',
            false,
            false
        );
        $resource->expects($this->any())->method('getTableName')->will($this->returnArgument(0));
        $resource->expects($this->any())->method('getConnection')->with()->will($this->returnValue($adapter));
        $eavEntityType = $this->getMock('Magento\Eav\Model\Resource\Entity\Type', array(), array(), '', false, false);
        $arguments = array(
            'resource' => $resource,
            'storeManager' => $storeManager,
            'eavEntityType' => $eavEntityType
        );
        $resourceModel = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute',
            array('getAdditionalAttributeTable'),
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
