<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractEntityTest extends TestCase
{
    /**
     * Entity model to be tested
     * @var AbstractEntity|MockObject
     */
    protected $_model;

    /** @var  Config */
    protected $eavConfig;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->eavConfig = $this->createMock(Config::class);
        $arguments =  $objectManager->getConstructArguments(
            AbstractEntity::class,
            ['eavConfig' => $this->eavConfig]
        );
        $this->_model = $this->getMockForAbstractClass(
            AbstractEntity::class,
            $arguments
        );
    }

    protected function tearDown(): void
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
        $attribute1 = $this->createPartialMock(Attribute::class, ['__wakeup']);
        $attribute1->setAttributeSetInfo([0 => $attribute1Sort]);
        $attribute2 = $this->createPartialMock(Attribute::class, ['__wakeup']);
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
                AbstractAttribute::class,
                ['getBackend', 'getBackendTable', '__wakeup']
            );
            $mock->setAttributeId($code);

            /** @var AbstractBackend $backendModel */
            $backendModel = $this->getMockBuilder(AbstractBackend::class)
                ->addMethods(['getBackend', 'getBackendTable'])
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

            $backendModel->setAttribute($mock);

            $mock->expects($this->any())->method('getBackend')->willReturn($backendModel);

            $mock->expects($this->any())->method('getBackendTable')->willReturn($code . '_table');

            $attributes[$code] = $mock;
        }
        return $attributes;
    }

    /**
     * Get adapter mock
     *
     * @return MockObject|Mysql
     */
    protected function _getConnectionMock()
    {
        $connection = $this->createPartialMock(Mysql::class, [
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

        $select = $this->createMock(Select::class);
        $select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $connection->expects($this->any())->method('query')->willReturn($statement);

        $connection->expects(
            $this->any()
        )->method(
            'describeTable'
        )->willReturn(
            ['value' => ['test']]
        );

        $connection->expects($this->any())->method('prepareColumnValue')->willReturnArgument(2);

        $connection->expects(
            $this->once()
        )->method(
            'delete'
        )->with(
            'test_table'
        )->willReturn(
            true
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
     * @return MockObject|AbstractAttribute
     */
    protected function _getAttributeMock($attributeCode, $attributeSetId)
    {
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->addMethods(['getApplyTo'])
            ->onlyMethods(['getBackend', 'getBackendTable', 'isInSet', 'getAttributeCode', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->setAttributeId($attributeCode);

        $attribute->expects(
            $this->any()
        )->method(
            'getBackendTable'
        )->willReturn(
            $attributeCode . '_table'
        );

        $attribute->expects(
            $this->any()
        )->method(
            'isInSet'
        )->with(
            $attributeSetId
        )->willReturn(
            false
        );

        $attribute->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);

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
            Product::class,
            ['getOrigData', '__wakeup', 'beforeSave', 'afterSave', 'validateBeforeSave']
        );
        $object->setEntityTypeId(1);
        foreach ($productData as $key => $value) {
            $object->setData($key, $value);
        }
        $object->expects($this->any())->method('getOrigData')->willReturn($productOrigData);

        $entityType = new DataObject();
        $entityType->setEntityTypeCode('test');
        $entityType->setEntityTypeId(0);
        $entityType->setEntityTable('table');

        $attributes = $this->_getAttributes();

        $attribute = $this->_getAttributeMock($attributeCode, $attributeSetId);

        /** @var AbstractBackend $backendModel */
        $backendModel = $this->getMockBuilder(AbstractBackend::class)
            ->addMethods(['getBackend', 'getBackendTable'])
            ->onlyMethods(['getAffectedFields', 'isStatic', 'getEntityValueId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $backendModel->expects(
            $this->once()
        )->method(
            'getAffectedFields'
        )->willReturn(
            ['test_table' => [['value_id' => 0, 'attribute_id' => $attributeCode]]]
        );

        $backendModel->expects($this->any())->method('isStatic')->willReturn(false);

        $backendModel->expects($this->never())->method('getEntityValueId');

        $backendModel->setAttribute($attribute);
        $attribute->expects($this->any())->method('getBackend')->willReturn($backendModel);
        $attribute->setId(222);
        $attributes[$attributeCode] = $attribute;
        $eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->eavConfig = $this->createMock(Config::class);
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
        /** @var AbstractEntity|MockObject $model */
        $model = $this->getMockBuilder(AbstractEntity::class)
            ->setConstructorArgs($arguments)
            ->addMethods(['_getValue'])
            ->onlyMethods(['beginTransaction', 'commit', 'rollback', 'getConnection'])
            ->getMock();
        $model->expects($this->any())->method('_getValue')->willReturn($eavConfig);
        $model->expects($this->any())->method('getConnection')->willReturn($this->_getConnectionMock());

        $eavConfig->expects($this->any())->method('getAttribute')->willReturnCallback(
            function ($entityType, $attributeCode) use ($attributes) {
                return $entityType && isset($attributes[$attributeCode]) ? $attributes[$attributeCode] : null;
            }
        );
        $model->isPartialSave(true);
        $model->save($object);
    }

    /**
     * @return array
     */
    public static function productAttributesDataProvider()
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

    public function testDuplicateExceptionProcessingOnSave()
    {
        $this->expectException('Magento\Framework\Exception\AlreadyExistsException');
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $connection->expects($this->once())->method('rollback');

        /** @var AbstractEntity|MockObject $model */
        $model = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection'])
            ->getMockForAbstractClass();
        $model->expects($this->any())->method('getConnection')->willReturn($connection);

        /** @var AbstractModel|MockObject $object */
        $object = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->once())->method('hasDataChanges')->willReturn(true);
        $object->expects($this->once())->method('beforeSave')->willThrowException(new DuplicateException());
        $object->expects($this->once())->method('setHasDataChanges')->with(true);

        $model->save($object);
    }
}
