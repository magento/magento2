<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AbstractEntityTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractEntityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Entity model to be tested
     * @var AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /** @var  \Magento\Eav\Model\Config */
    protected $eavConfig;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $arguments =  $objectManager->getConstructArguments(
            AbstractEntity::class,
            ['eavConfig' => $this->eavConfig]
        );
        $this->_model = $this->getMockForAbstractClass(
            AbstractEntity::class,
            $arguments
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
        $attribute1 = $this->createPartialMock(\Magento\Eav\Model\Entity\Attribute::class, ['__wakeup']);
        $attribute1->setAttributeSetInfo([0 => $attribute1Sort]);
        $attribute2 = $this->createPartialMock(\Magento\Eav\Model\Entity\Attribute::class, ['__wakeup']);
        $attribute2->setAttributeSetInfo([0 => $attribute2Sort]);
        $this->assertEquals($expected, $this->_model->attributesCompare($attribute1, $attribute2));
    }

    /**
     * @return array
     */
    public static function compareAttributesDataProvider()
    {
        return [
            'attribute1 bigger than attribute2' => [
                'attribute1Sort' => ['group_sort' => 7, 'sort' => 5],
                'attribute2Sort' => ['group_sort' => 5, 'sort' => 10],
                'expected' => 1,
            ],
            'attribute1 smaller than attribute2' => [
                'attribute1Sort' => ['group_sort' => 7, 'sort' => 5],
                'attribute2Sort' => ['group_sort' => 7, 'sort' => 10],
                'expected' => -1,
            ],
            'attribute1 equals to attribute2' => [
                'attribute1Sort' => ['group_sort' => 7, 'sort' => 5],
                'attribute2Sort' => ['group_sort' => 7, 'sort' => 5],
                'expected' => 0,
            ]
        ];
    }

    /**
     * Get attribute list
     *
     * @return array
     */
    protected function _getAttributes()
    {
        $attributes = [];
        $codes = ['entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id'];
        foreach ($codes as $code) {
            $mock = $this->createPartialMock(
                \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
                ['getBackend', 'getBackendTable', '__wakeup']
            );
            $mock->setAttributeId($code);

            /** @var $backendModel \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend */
            $backendModel = $this->createPartialMock(
                \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class,
                ['getBackend', 'getBackendTable']
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
    protected function _getConnectionMock()
    {
        $connection = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [
                'describeTable',
                'getIndexList',
                'lastInsertId',
                'insert',
                'prepareColumnValue',
                'select',
                'query',
                'delete'
            ]);
        $statement = $this->createPartialMock(
            \Zend_Db_Statement::class,
            ['closeCursor', 'columnCount', 'errorCode', 'errorInfo', 'fetch', 'nextRowset', 'rowCount']
        );

        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $connection->expects($this->any())->method('query')->will($this->returnValue($statement));

        $connection->expects(
            $this->any()
        )->method(
            'describeTable'
        )->will(
            $this->returnValue(['value' => ['test']])
        );

        $connection->expects($this->any())->method('prepareColumnValue')->will($this->returnArgument(2));

        $connection->expects(
            $this->once()
        )->method(
            'delete'
        )->with(
            $this->equalTo('test_table')
        )->will(
            $this->returnValue(true)
        );

        $connection->expects($this->any())
            ->method('select')
            ->willReturn($select);

        $connection->expects($this->any())
            ->method('getIndexList')
            ->willReturn(
                [
                    'PK_ENTITYTABLE' => [
                        'COLUMNS_LIST' => [
                            'entity_id'
                        ]
                    ]
                ]
            );

        return $connection;
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
        $attribute = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getBackend', 'getBackendTable', 'isInSet', 'getApplyTo', 'getAttributeCode', '__wakeup']
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
        $object = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getOrigData', '__wakeup', 'beforeSave', 'afterSave', 'validateBeforeSave']
        );
        $object->setEntityTypeId(1);
        foreach ($productData as $key => $value) {
            $object->setData($key, $value);
        }
        $object->expects($this->any())->method('getOrigData')->will($this->returnValue($productOrigData));

        $entityType = new \Magento\Framework\DataObject();
        $entityType->setEntityTypeCode('test');
        $entityType->setEntityTypeId(0);
        $entityType->setEntityTable('table');

        $attributes = $this->_getAttributes();

        $attribute = $this->_getAttributeMock($attributeCode, $attributeSetId);

        /** @var $backendModel \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend */
        $backendModel = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class,
            [
                'getBackend',
                'getBackendTable',
                'getAffectedFields',
                'isStatic',
                'getEntityValueId',
            ]
        );

        $backendModel->expects(
            $this->once()
        )->method(
            'getAffectedFields'
        )->will(
            $this->returnValue(['test_table' => [['value_id' => 0, 'attribute_id' => $attributeCode]]])
        );

        $backendModel->expects($this->any())->method('isStatic')->will($this->returnValue(false));

        $backendModel->expects($this->never())->method('getEntityValueId');

        $backendModel->setAttribute($attribute);
        $attribute->expects($this->any())->method('getBackend')->will($this->returnValue($backendModel));
        $attribute->setId(222);
        $attributes[$attributeCode] = $attribute;
        $eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $arguments =  $objectManager->getConstructArguments(
            AbstractEntity::class,
            [
                'eavConfig' => $eavConfig,
                'data' => [
                    'type' => $entityType,
                    'entityTable' => 'entityTable',
                    'attributesByCode' => $attributes
                ]
            ]
        );
        /** @var $model AbstractEntity|\PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockBuilder(AbstractEntity::class)
            ->setConstructorArgs($arguments)
            ->setMethods(['_getValue', 'beginTransaction', 'commit', 'rollback', 'getConnection'])
            ->getMock();
        $model->expects($this->any())->method('_getValue')->will($this->returnValue($eavConfig));
        $model->expects($this->any())->method('getConnection')->will($this->returnValue($this->_getConnectionMock()));

        $eavConfig->expects($this->any())->method('getAttribute')->will(
            $this->returnCallback(
                function ($entityType, $attributeCode) use ($attributes) {
                    return $entityType && isset($attributes[$attributeCode]) ? $attributes[$attributeCode] : null;
                }
            )
        );
        $model->isPartialSave(true);
        $model->save($object);
    }

    /**
     * @return array
     */
    public function productAttributesDataProvider()
    {
        $attributeSetId = 10;
        return [
            [
                'test_attr',
                $attributeSetId,
                [
                    'test_attr' => 'test_attr',
                    'attribute_set_id' => $attributeSetId,
                    'entity_id' => null,
                    'store_id' => 1
                ],
                null,
            ],
            [
                'test_attr',
                $attributeSetId,
                [
                    'test_attr' => 'test_attr',
                    'attribute_set_id' => $attributeSetId,
                    'entity_id' => 12345,
                    'store_id' => 1
                ],
                ['test_attr' => 'test_attr']
            ],
            [
                'test_attr',
                $attributeSetId,
                ['test_attr' => '99.99', 'attribute_set_id' => $attributeSetId, 'entity_id' => 12345, 'store_id' => 1],
                ['test_attr' => '99.9900']
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testDuplicateExceptionProcessingOnSave()
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('rollback');

        /** @var AbstractEntity|\PHPUnit_Framework_MockObject_MockObject $model */
        $model = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMockForAbstractClass();
        $model->expects($this->any())->method('getConnection')->willReturn($connection);

        /** @var AbstractModel|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->once())->method('hasDataChanges')->willReturn(true);
        $object->expects($this->once())->method('beforeSave')->willThrowException(new DuplicateException());
        $object->expects($this->once())->method('setHasDataChanges')->with(true);

        $model->save($object);
    }
}
