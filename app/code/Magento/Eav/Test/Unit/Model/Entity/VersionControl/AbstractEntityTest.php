<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\VersionControl;

use Magento\Eav\Model\Entity\VersionControl\AbstractEntity;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for version control abstract entity model.
 */
class AbstractEntityTest extends \Magento\Eav\Test\Unit\Model\Entity\AbstractEntityTest
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshot;

    /**
     * @var RelationComposite|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityRelationComposite;

    protected function setUp(): void
    {
        $this->entitySnapshot = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class,
            ['isModified', 'registerSnapshot']
        );

        $this->entityRelationComposite = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class,
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
            \Magento\Catalog\Model\Product::class,
            ['getOrigData', '__wakeup', 'beforeSave', 'afterSave', 'validateBeforeSave']
        );

        $object->setEntityTypeId(1);
        foreach ($productData as $key => $value) {
            $object->setData($key, $value);
        }
        $object->expects($this->any())->method('getOrigData')->willReturn($productOrigData);

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
        )->willReturn(
            ['test_table' => [['value_id' => 0, 'attribute_id' => $attributeCode]]]
        );

        $backendModel->expects($this->any())->method('isStatic')->willReturn(false);
        $backendModel->expects($this->never())->method('getEntityValueId');
        $backendModel->setAttribute($attribute);

        $attribute->expects($this->any())->method('getBackend')->willReturn($backendModel);
        $attribute->setId(222);
        $attributes[$attributeCode] = $attribute;
        $eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->entitySnapshot->expects($this->once())->method('isModified')->willReturn(true);
        $this->entitySnapshot->expects($this->once())->method('registerSnapshot')->with($object);

        $this->entityRelationComposite->expects($this->once())->method('processRelations')->with($object);

        $arguments =  $objectManager->getConstructArguments(
            \Magento\Eav\Model\Entity\VersionControl\AbstractEntity::class,
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

        /** @var $model AbstractEntity|\PHPUnit\Framework\MockObject\MockObject */
        $model = $this->getMockBuilder(\Magento\Eav\Model\Entity\VersionControl\AbstractEntity::class)
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

        /** @var $object \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject */
        $object = $this->createMock(\Magento\Catalog\Model\Product::class);

        $arguments = $objectManager->getConstructArguments(
            \Magento\Eav\Model\Entity\VersionControl\AbstractEntity::class,
            [
                'entitySnapshot' => $this->entitySnapshot,
                'entityRelationComposite' => $this->entityRelationComposite,
            ]
        );

        /** @var $model AbstractEntity|\PHPUnit\Framework\MockObject\MockObject */
        $model = $this->getMockBuilder(\Magento\Eav\Model\Entity\VersionControl\AbstractEntity::class)
            ->setConstructorArgs($arguments)
            ->setMethods(['beginTransaction', 'commit'])
            ->getMock();

        $this->entitySnapshot->expects($this->once())->method('isModified')->willReturn(false);
        $this->entitySnapshot->expects($this->never())->method('registerSnapshot');

        $this->entityRelationComposite->expects($this->once())->method('processRelations')->with($object);

        $model->save($object);
    }
}
