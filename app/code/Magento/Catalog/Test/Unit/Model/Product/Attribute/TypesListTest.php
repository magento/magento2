<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeTypeInterface;
use Magento\Catalog\Api\Data\ProductAttributeTypeInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Inputtype;
use Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory;
use Magento\Catalog\Model\Product\Attribute\TypesList;
use Magento\Framework\Api\DataObjectHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypesListTest extends TestCase
{
    /**
     * @var TypesList
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $inputTypeFactoryMock;

    /**
     * @var MockObject
     */
    protected $attributeTypeFactoryMock;

    /**
     * @var MockObject|DataObjectHelper
     */
    protected $dataObjectHelperMock;

    protected function setUp(): void
    {
        $this->inputTypeFactoryMock = $this->createPartialMock(
            InputtypeFactory::class,
            ['create']
        );
        $this->attributeTypeFactoryMock =
            $this->createPartialMock(ProductAttributeTypeInterfaceFactory::class, [
                'create',
            ]);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new TypesList(
            $this->inputTypeFactoryMock,
            $this->attributeTypeFactoryMock,
            $this->dataObjectHelperMock
        );
    }

    public function testGetItems()
    {
        $inputTypeMock = $this->createMock(Inputtype::class);
        $this->inputTypeFactoryMock->expects($this->once())->method('create')->willReturn($inputTypeMock);
        $inputTypeMock->expects($this->once())->method('toOptionArray')->willReturn(['option' => ['value']]);
        $attributeTypeMock = $this->getMockForAbstractClass(ProductAttributeTypeInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($attributeTypeMock, ['value'], ProductAttributeTypeInterface::class)
            ->willReturnSelf();
        $this->attributeTypeFactoryMock->expects($this->once())->method('create')->willReturn($attributeTypeMock);
        $this->assertEquals([$attributeTypeMock], $this->model->getItems());
    }
}
