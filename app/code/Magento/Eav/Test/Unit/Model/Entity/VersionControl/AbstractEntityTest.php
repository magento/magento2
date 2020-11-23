<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\VersionControl;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\VersionControl\AbstractEntity;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for version control abstract entity model.
 */
class AbstractEntityTest extends \Magento\Eav\Test\Unit\Model\Entity\AbstractEntityTest
{
    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshot;

    /**
     * @var RelationComposite|MockObject
     */
    protected $entityRelationComposite;

    protected function setUp(): void
    {
        $this->entitySnapshot = $this->createPartialMock(
            Snapshot::class,
            ['isModified', 'registerSnapshot']
        );

        $this->entityRelationComposite = $this->createPartialMock(
            RelationComposite::class,
            ['processRelations']
        );

        parent::setUp();
    }

    /**
     * @param string $attributeCode
     * @param int $attributeSetId
     * @param array $productData
     * @param array $productOrigData
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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

        $this->entitySnapshot->expects($this->once())->method('isModified')->willReturn(true);
        $this->entitySnapshot->expects($this->once())->method('registerSnapshot')->with($object);

        $this->entityRelationComposite->expects($this->once())->method('processRelations')->with($object);

        $arguments =  $objectManager->getConstructArguments(
            AbstractEntity::class,
            [
                'eavConfig' => $eavConfig,
                'entitySnapshot' => $this->entitySnapshot,
                'entityRelationComposite' => $this->entityRelationComposite,
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
            ->setMethods(['_getValue', 'beginTransaction', 'commit', 'rollback', 'getConnection'])
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

    public function testSaveNotModified()
    {
        $objectManager = new ObjectManager($this);

        /** @var Product|MockObject $object */
        $object = $this->createMock(Product::class);

        $arguments = $objectManager->getConstructArguments(
            AbstractEntity::class,
            [
                'entitySnapshot' => $this->entitySnapshot,
                'entityRelationComposite' => $this->entityRelationComposite,
            ]
        );

        /** @var AbstractEntity|MockObject $model */
        $model = $this->getMockBuilder(AbstractEntity::class)
            ->setConstructorArgs($arguments)
            ->setMethods(['beginTransaction', 'commit'])
            ->getMock();

        $this->entitySnapshot->expects($this->once())->method('isModified')->willReturn(false);
        $this->entitySnapshot->expects($this->never())->method('registerSnapshot');

        $this->entityRelationComposite->expects($this->once())->method('processRelations')->with($object);

        $model->save($object);
    }
}
