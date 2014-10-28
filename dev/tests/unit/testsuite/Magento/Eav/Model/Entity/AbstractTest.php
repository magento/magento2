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
namespace Magento\Eav\Model\Entity;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Entity model to be tested
     * @var \Magento\Eav\Model\Entity\AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /** @var  \Magento\Eav\Model\Config */
    protected $eavConfig;

    protected function setUp()
    {

        $this->eavConfig = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\AbstractEntity',
            array(
                $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false),
                $this->eavConfig,
                $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', array(), array(), '', false),
                $this->getMock('\Magento\Framework\Locale\FormatInterface'),
                $this->getMock('Magento\Eav\Model\Resource\Helper', array(), array(), '', false),
                $this->getMock('Magento\Framework\Validator\UniversalFactory', array(), array(), '', false)
            )
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @param array $attribute1Sort
     * @param array $attribute2Sort
     * @param float $expected
     *
     * @dataProvider compareAttributesDataProvider
     */
    public function testCompareAttributes($attribute1Sort, $attribute2Sort, $expected)
    {
        $attribute1 = $this->getMock('Magento\Eav\Model\Entity\Attribute', array('__wakeup'), array(), '', false);
        $attribute1->setAttributeSetInfo(array(0 => $attribute1Sort));
        $attribute2 = $this->getMock('Magento\Eav\Model\Entity\Attribute', array('__wakeup'), array(), '', false);
        $attribute2->setAttributeSetInfo(array(0 => $attribute2Sort));
        $this->assertEquals($expected, $this->_model->attributesCompare($attribute1, $attribute2));
    }

    public static function compareAttributesDataProvider()
    {
        return array(
            'attribute1 bigger than attribute2' => array(
                'attribute1Sort' => array('group_sort' => 7, 'sort' => 5),
                'attribute2Sort' => array('group_sort' => 5, 'sort' => 10),
                'expected' => 1
            ),
            'attribute1 smaller than attribute2' => array(
                'attribute1Sort' => array('group_sort' => 7, 'sort' => 5),
                'attribute2Sort' => array('group_sort' => 7, 'sort' => 10),
                'expected' => -1
            ),
            'attribute1 equals to attribute2' => array(
                'attribute1Sort' => array('group_sort' => 7, 'sort' => 5),
                'attribute2Sort' => array('group_sort' => 7, 'sort' => 5),
                'expected' => 0
            )
        );
    }

    /**
     * Get attribute list
     *
     * @return array
     */
    protected function _getAttributes()
    {
        $attributes = array();
        $codes = array('entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id');
        foreach ($codes as $code) {
            $mock = $this->getMock(
                'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
                array('getBackend', 'getBackendTable', '__wakeup'),
                array(),
                '',
                false
            );
            $mock->setAttributeId($code);

            $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
            /** @var $backendModel \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend */
            $backendModel = $this->getMock(
                'Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend',
                array('getBackend', 'getBackendTable'),
                array($logger)
            );

            $backendModel->setAttribute($mock);

            $mock->expects($this->any())->method('getBackend')->will($this->returnValue($backendModel));

            $mock->expects($this->any())->method('getBackendTable')->will($this->returnValue($code . '_table'));

            $attributes[$code] = $mock;
        }
        return $attributes;
    }

    /**
     * Get adapter mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    private function _getAdapterMock()
    {
        $adapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('describeTable', 'lastInsertId', 'insert', 'prepareColumnValue', 'query', 'delete'),
            array(),
            '',
            false
        );
        $statement = $this->getMock(
            'Zend_Db_Statement',
            array('closeCursor', 'columnCount', 'errorCode', 'errorInfo', 'fetch', 'nextRowset', 'rowCount'),
            array(),
            '',
            false
        );

        $adapter->expects($this->any())->method('query')->will($this->returnValue($statement));

        $adapter->expects(
            $this->any()
        )->method(
            'describeTable'
        )->will(
            $this->returnValue(array('value' => array('test')))
        );

        $adapter->expects($this->any())->method('prepareColumnValue')->will($this->returnArgument(2));

        $adapter->expects(
            $this->once()
        )->method(
            'delete'
        )->with(
            $this->equalTo('test_table')
        )->will(
            $this->returnValue(true)
        );

        return $adapter;
    }

    /**
     * Get attribute mock
     *
     * @param string $attributeCode
     * @param int $attributeSetId
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected function _getAttributeMock($attributeCode, $attributeSetId)
    {
        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array('getBackend', 'getBackendTable', 'isInSet', 'getApplyTo', 'getAttributeCode', '__wakeup'),
            array(),
            '',
            false
        );
        $attribute->setAttributeId($attributeCode);

        $attribute->expects(
            $this->any()
        )->method(
            'getBackendTable'
        )->will(
            $this->returnValue($attributeCode . '_table')
        );

        $attribute->expects(
            $this->any()
        )->method(
            'isInSet'
        )->with(
            $this->equalTo($attributeSetId)
        )->will(
            $this->returnValue(false)
        );

        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));

        return $attribute;
    }

    /**
     * @param string $attributeCode
     * @param int $attributeSetId
     * @param array $productData
     * @param array $productOrigData
     *
     * @dataProvider productAttributesDataProvider
     */
    public function testSave($attributeCode, $attributeSetId, $productData, $productOrigData)
    {
        $object = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getOrigData', '__wakeup'),
            array(),
            '',
            false
        );
        $object->setEntityTypeId(1);
        $object->setData($productData);
        $object->expects($this->any())->method('getOrigData')->will($this->returnValue($productOrigData));

        $entityType = new \Magento\Framework\Object();
        $entityType->setEntityTypeCode('test');
        $entityType->setEntityTypeId(0);
        $entityType->setEntityTable('table');

        $attributes = $this->_getAttributes();

        $attribute = $this->_getAttributeMock($attributeCode, $attributeSetId);

        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        /** @var $backendModel \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend */
        $backendModel = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend',
            array(
                'getBackend',
                'getBackendTable',
                'getAffectedFields',
                'isStatic',
                'getEntityValueId',
                'getEntityIdField'
            ),
            array($logger)
        );

        $backendModel->expects(
            $this->once()
        )->method(
            'getAffectedFields'
        )->will(
            $this->returnValue(array('test_table' => array(array('value_id' => 0, 'attribute_id' => $attributeCode))))
        );

        $backendModel->expects($this->any())->method('isStatic')->will($this->returnValue(false));

        $backendModel->expects($this->never())->method('getEntityValueId');

        $backendModel->expects(
            isset($productData['entity_id']) ? $this->never() : $this->once()
        )->method(
            'getEntityIdField'
        )->will(
            $this->returnValue('entity_id')
        );

        $backendModel->setAttribute($attribute);

        $attribute->expects($this->any())->method('getBackend')->will($this->returnValue($backendModel));
        $attribute->setId(222);

        $attributes[$attributeCode] = $attribute;

        $eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $data = array(
            $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false),
            $eavConfig,
            $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', array(), array(), '', false),
            $this->getMock('Magento\Framework\Locale\FormatInterface'),
            $this->getMock('Magento\Eav\Model\Resource\Helper', array(), array(), '', false),
            $this->getMock('Magento\Framework\Validator\UniversalFactory', array(), array(), '', false),
            array('type' => $entityType, 'entityTable' => 'entityTable', 'attributesByCode' => $attributes)
        );
        /** @var $model \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->setConstructorArgs($data)
            ->setMethods(['_getValue'])
            ->getMock();

        $model->expects($this->any())->method('_getValue')->will($this->returnValue($eavConfig));
        $eavConfig->expects($this->any())->method('getAttribute')->will(
            $this->returnCallback(
                function ($entityType, $attributeCode) use ($attributes) {
                    return $entityType && isset($attributes[$attributeCode]) ? $attributes[$attributeCode] : null;
                }
            )
        );

        $model->setConnection($this->_getAdapterMock());
        $model->isPartialSave(true);

        $model->save($object);
    }

    public function productAttributesDataProvider()
    {
        $attributeSetId = 10;
        return array(
            array(
                'test_attr',
                $attributeSetId,
                array('test_attr' => 'test_attr', 'attribute_set_id' => $attributeSetId, 'entity_id' => null),
                null
            ),
            array(
                'test_attr',
                $attributeSetId,
                array('test_attr' => 'test_attr', 'attribute_set_id' => $attributeSetId, 'entity_id' => 12345),
                array('test_attr' => 'test_attr')
            ),
            array(
                'test_attr',
                $attributeSetId,
                array('test_attr' => '99.99', 'attribute_set_id' => $attributeSetId, 'entity_id' => 12345),
                array('test_attr' => '99.9900')
            )
        );
    }
}
