<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\SetManagement;
use Magento\Eav\Api\AttributeSetManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetManagementTest extends TestCase
{
    /**
     * @var SetManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $attrSetManagementMock;

    /**
     * @var MockObject
     */
    protected $attributeSetRepository;

    /**
     * @var MockObject
     */
    protected $eavConfig;

    protected function setUp(): void
    {
        $this->attrSetManagementMock = $this->getMockForAbstractClass(AttributeSetManagementInterface::class);
        $this->attributeSetRepository = $this->getMockForAbstractClass(AttributeSetRepositoryInterface::class);
        $this->eavConfig = $this->createMock(Config::class);
        $this->model = new SetManagement(
            $this->attrSetManagementMock,
            $this->attributeSetRepository,
            $this->eavConfig
        );
    }

    public function testCreate()
    {
        $skeletonId = 1;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $skeletonSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);

        $this->attributeSetRepository->expects($this->once())
            ->method('get')
            ->with($skeletonId)
            ->willReturn($skeletonSetMock);

        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->once())->method('getId')->willReturn(4);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Product::ENTITY)
            ->willReturn($typeMock);
        $skeletonSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(4);

        $this->attrSetManagementMock->expects($this->once())
            ->method('create')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSetMock,
                $skeletonId
            )->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->create($attributeSetMock, $skeletonId));
    }

    public function testCreateNonProductAttributeSet()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $skeletonId = 1;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $skeletonSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->attributeSetRepository->expects($this->once())
            ->method('get')
            ->with($skeletonId)
            ->willReturn($skeletonSetMock);

        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->once())->method('getId')->willReturn(4);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Product::ENTITY)
            ->willReturn($typeMock);
        $skeletonSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(3);
        $this->model->create($attributeSetMock, $skeletonId);

        $this->expectExceptionMessage(
            "The attribute set couldn't be created because it's based on a non-product attribute set."
        );
    }
}
