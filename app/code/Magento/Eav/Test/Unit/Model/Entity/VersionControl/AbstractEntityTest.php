<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshot;

    /**
     * @var RelationComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityRelationComposite;

    protected function setUp()
    {
        $this->entitySnapshot = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot',
            ['isModified', 'registerSnapshot'],
            [],
            '',
            false
        );

        $this->entityRelationComposite = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite',
            ['processRelations'],
            [],
            '',
            false
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
        $object = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getOrigData', '__wakeup', 'beforeSave', 'afterSave', 'validateBeforeSave'],
            [],
            '',
            false
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
        $backendModel = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend',
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
        $eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->entitySnapshot->expects($this->once())->method('isModified')->willReturn(true);
        $this->entitySnapshot->expects($this->once())->method('registerSnapshot')->with($object);

        $this->entityRelationComposite->expects($this->once())->method('processRelations')->with($object);

        $arguments =  $objectManager->getConstructArguments(
            'Magento\Eav\Model\Entity\VersionControl\AbstractEntity',
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

        /** @var $model AbstractEntity|\PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockBuilder('Magento\Eav\Model\Entity\VersionControl\AbstractEntity')
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

    public function testSaveNotModified()
    {
        $objectManager = new ObjectManager($this);

        /** @var $object \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
        $object = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);

        $arguments = $objectManager->getConstructArguments(
            'Magento\Eav\Model\Entity\VersionControl\AbstractEntity',
            [
                'entitySnapshot' => $this->entitySnapshot,
                'entityRelationComposite' => $this->entityRelationComposite,
            ]
        );

        /** @var $model AbstractEntity|\PHPUnit_Framework_MockObject_MockObject */
        $model = $this->getMockBuilder('Magento\Eav\Model\Entity\VersionControl\AbstractEntity')
            ->setConstructorArgs($arguments)
            ->setMethods(['beginTransaction', 'commit'])
            ->getMock();

        $this->entitySnapshot->expects($this->once())->method('isModified')->willReturn(false);
        $this->entitySnapshot->expects($this->never())->method('registerSnapshot');

        $this->entityRelationComposite->expects($this->once())->method('processRelations')->with($object);

        $model->save($object);
    }
}
