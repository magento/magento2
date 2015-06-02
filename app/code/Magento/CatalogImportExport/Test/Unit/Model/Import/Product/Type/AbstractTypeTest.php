<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test class for import product AbstractType class
 */
class AbstractTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityModel;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\Simple
     */
    protected $simpleType;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->entityModel = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product',
            [],
            [],
            '',
            false
        );
        $attrSetColFactory = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $attrSetCollection = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection',
            [],
            [],
            '',
            false
        );
        $attrColFactory = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $attributeSet = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            [],
            [],
            '',
            false
        );
        $attrCollection = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Collection',
            [],
            [],
            '',
            false
        );
        $attribute = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute',
            [
                'getAttributeCode',
                'getId',
                'getIsVisible',
                'getIsGlobal',
                'getIsRequired',
                'getIsUnique',
                'getFrontendLabel',
                'isStatic',
                'getApplyTo',
                'getDefaultValue',
                'usesSource',
                'getFrontendInput',
            ],
            [],
            '',
            false
        );

        $this->entityModel->expects($this->any())->method('getEntityTypeId')->willReturn(3);
        $this->entityModel->expects($this->any())->method('getAttributeOptions')->willReturn(['option1', 'option2']);
        $attrSetColFactory->expects($this->any())->method('create')->willReturn($attrSetCollection);
        $attrSetCollection->expects($this->any())->method('setEntityTypeFilter')->willReturn([$attributeSet]);
        $attrColFactory->expects($this->any())->method('create')->willReturn($attrCollection);
        $attrCollection->expects($this->any())->method('setAttributeSetFilter')->willReturn([$attribute]);
        $attributeSet->expects($this->any())->method('getId')->willReturn(1);
        $attributeSet->expects($this->any())->method('getAttributeSetName')->willReturn('attribute_set_name');
        $attribute->expects($this->any())->method('getAttributeCode')->willReturn('attr_code');
        $attribute->expects($this->any())->method('getId')->willReturn('1');
        $attribute->expects($this->any())->method('getIsVisible')->willReturn(true);
        $attribute->expects($this->any())->method('getIsGlobal')->willReturn(true);
        $attribute->expects($this->any())->method('getIsRequired')->willReturn(true);
        $attribute->expects($this->any())->method('getIsUnique')->willReturn(true);
        $attribute->expects($this->any())->method('getFrontendLabel')->willReturn('frontend_label');
        $attribute->expects($this->any())->method('isStatic')->willReturn(true);
        $attribute->expects($this->any())->method('getApplyTo')->willReturn(['simple']);
        $attribute->expects($this->any())->method('getDefaultValue')->willReturn('default_value');
        $attribute->expects($this->any())->method('usesSource')->willReturn(true);
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn('multiselect');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->simpleType = $this->objectManagerHelper->getObject(
            'Magento\CatalogImportExport\Model\Import\Product\Type\Simple',
            [
                'attrSetColFac' => $attrSetColFactory,
                'prodAttrColFac' => $attrColFactory,
                'params' => [$this->entityModel, 'simple'],
            ]
        );
    }

    public function testIsRowValidSuccess()
    {
        $rowData = ['_attribute_set' => 'attribute_set_name'];
        $rowNum = 1;
        $this->entityModel->expects($this->any())->method('getRowScope')->willReturn(null);
        $this->entityModel->expects($this->never())->method('addRowError');
        $this->assertTrue($this->simpleType->isRowValid($rowData, $rowNum));
    }

    public function testIsRowValidError()
    {
        $rowData = ['_attribute_set' => 'attribute_set_name'];
        $rowNum = 1;
        $this->entityModel->expects($this->any())->method('getRowScope')->willReturn(1);
        $this->entityModel->expects($this->once())->method('addRowError')
            ->with(
                \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_VALUE_IS_REQUIRED,
                1,
                'attr_code'
            )
            ->willReturnSelf();
        $this->assertFalse($this->simpleType->isRowValid($rowData, $rowNum));
    }
}
