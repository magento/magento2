<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\MetadataObjectInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    private $metadataService;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->setFactoryMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\SetFactory::class,
            ['create', '__wakeup']
        );
        $this->typeFactoryMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\TypeFactory::class,
            ['create', '__wakeup']
        );

        $this->metadataService = $this->createMock(ProductAttributeRepositoryInterface::class);

        $entityTypeMock = $this->createPartialMock(\Magento\Eav\Model\Entity\Type::class, ['getEntityModel']);
        $entityTypeMock->method('getEntityModel')->willReturn(Product::class);
        $eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $eavConfigMock->method('getEntityType')->willReturn($entityTypeMock);

        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\ResourceModel\Product::class,
            [
                'setFactory' => $this->setFactoryMock,
                'typeFactory' => $this->typeFactoryMock,
                'eavConfig' => $eavConfigMock,
                'metadataService' => $this->metadataService,
            ]
        );
    }

    public function testValidateWrongAttributeSet()
    {
        $productTypeId = 4;
        $expectedErrorMessage = ['attribute_set' => 'Invalid attribute set entity type'];

        $productMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getAttributeSetId', '__wakeup']
        );
        $attributeSetMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Set::class,
            ['load', 'getEntityTypeId', '__wakeup']
        );
        $entityTypeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);

        $this->typeFactoryMock->expects($this->once())->method('create')->will($this->returnValue($entityTypeMock));
        $entityTypeMock->expects($this->once())->method('loadByCode')->with('catalog_product')->willReturnSelf();

        $productAttributeSetId = 4;
        $productMock->expects($this->once())->method('getAttributeSetId')
            ->will($this->returnValue($productAttributeSetId));

        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $attributeSetMock->expects($this->once())->method('load')->with($productAttributeSetId)->willReturnSelf();

        //attribute set of wrong type
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->will($this->returnValue(3));
        $entityTypeMock->expects($this->once())->method('getId')->will($this->returnValue($productTypeId));

        $this->assertEquals($expectedErrorMessage, $this->model->validate($productMock));
    }

    public function testGetCustomAttributes()
    {
        $priceCode = 'price';
        $colorAttributeCode = 'color';
        $interfaceAttribute = $this->createMock(MetadataObjectInterface::class);
        $interfaceAttribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($priceCode);
        $colorAttribute = $this->createMock(MetadataObjectInterface::class);
        $colorAttribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($colorAttributeCode);
        $customAttributesMetadata = [$interfaceAttribute, $colorAttribute];

        $this->metadataService->expects($this->once())
            ->method('getCustomAttributesMetadata')
            ->willReturn($customAttributesMetadata);

        $this->assertEquals([$colorAttributeCode], $this->model->getCustomAttributesCodes());
    }
}
