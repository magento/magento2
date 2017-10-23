<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

class SetManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\SetManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrSetManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    protected function setUp()
    {
        $this->attrSetManagementMock = $this->createMock(\Magento\Eav\Api\AttributeSetManagementInterface::class);
        $this->attributeSetRepository = $this->createMock(\Magento\Eav\Api\AttributeSetRepositoryInterface::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->model = new \Magento\Catalog\Model\Product\Attribute\SetManagement(
            $this->attrSetManagementMock,
            $this->attributeSetRepository,
            $this->eavConfig
        );
    }

    public function testCreate()
    {
        $skeletonId = 1;
        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $skeletonSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);

        $this->attributeSetRepository->expects($this->once())
            ->method('get')
            ->with($skeletonId)
            ->willReturn($skeletonSetMock);

        $typeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $typeMock->expects($this->once())->method('getId')->willReturn(4);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->willReturn($typeMock);
        $skeletonSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(4);

        $this->attrSetManagementMock->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeSetMock,
                $skeletonId
            )->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->create($attributeSetMock, $skeletonId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Can not create attribute set based on non product attribute set.
     */
    public function testCreateNonProductAttributeSet()
    {
        $skeletonId = 1;
        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $skeletonSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->attributeSetRepository->expects($this->once())
            ->method('get')
            ->with($skeletonId)
            ->willReturn($skeletonSetMock);

        $typeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $typeMock->expects($this->once())->method('getId')->willReturn(4);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->willReturn($typeMock);
        $skeletonSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(3);
        $this->model->create($attributeSetMock, $skeletonId);
    }
}
