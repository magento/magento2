<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $setFactoryMock;

    /**
     * @var MockObject
     */
    protected $typeFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->setFactoryMock = $this->createPartialMock(
            SetFactory::class,
            ['create', '__wakeup']
        );
        $this->typeFactoryMock = $this->createPartialMock(
            TypeFactory::class,
            ['create', '__wakeup']
        );

        $this->model = $objectManager->getObject(
            Product::class,
            [
                'setFactory' => $this->setFactoryMock,
                'typeFactory' => $this->typeFactoryMock,
            ]
        );
    }

    public function testValidateWrongAttributeSet()
    {
        $productTypeId = 4;
        $expectedErrorMessage = ['attribute_set' => 'Invalid attribute set entity type'];

        $productMock = $this->createPartialMock(
            DataObject::class,
            ['getAttributeSetId', '__wakeup']
        );
        $attributeSetMock = $this->createPartialMock(
            Set::class,
            ['load', 'getEntityTypeId', '__wakeup']
        );
        $entityTypeMock = $this->createMock(Type::class);

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
}
