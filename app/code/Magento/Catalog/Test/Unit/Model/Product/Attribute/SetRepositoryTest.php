<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\SetRepository
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attrSetRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavConfig;

    protected function setUp(): void
    {
        $this->attrSetRepositoryMock = $this->createMock(\Magento\Eav\Api\AttributeSetRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);

        $this->model = new \Magento\Catalog\Model\Product\Attribute\SetRepository(
            $this->attrSetRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->eavConfig
        );
    }

    public function testSave()
    {
        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 4);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('save')
            ->with($attributeSetMock)
            ->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->save($attributeSetMock));
    }

    /**
     */
    public function testSaveNonProductAttributeSet()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('Provided Attribute set non product Attribute set.');

        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 3);
        $this->attrSetRepositoryMock->expects($this->never())->method('save');
        $this->model->save($attributeSetMock);
    }

    public function testGet()
    {
        $attributeSetId = 1;
        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 4);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->get($attributeSetId));
    }

    /**
     */
    public function testGetNonProductAttributeSet()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('Provided Attribute set non product Attribute set.');

        $attributeSetId = 1;
        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 3);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->get($attributeSetId));
    }

    public function testDelete()
    {
        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 4);
        $this->attrSetRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($attributeSetMock)
            ->willReturn(true);
        $this->assertTrue($this->model->delete($attributeSetMock));
    }

    /**
     */
    public function testDeleteNonProductAttributeSet()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('Provided Attribute set non product Attribute set.');

        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 3);
        $this->attrSetRepositoryMock->expects($this->never())
            ->method('delete');
        $this->assertTrue($this->model->delete($attributeSetMock));
    }

    public function testDeleteById()
    {
        $attributeSetId = 1;
        $attributeSetMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 4);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($attributeSetId)
            ->willReturn(true);
        $this->assertTrue($this->model->deleteById($attributeSetId));
    }

    public function testGetList()
    {
        $searchResultMock = $this->createMock(\Magento\Eav\Api\Data\AttributeSetSearchResultsInterface::class);

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn(1);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn(2);

        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('entity_type_code')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->with([$filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setCurrentPage')
            ->with(1)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with(2)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class));

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);
        $this->assertEquals($searchResultMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Set mock for attribute set validation
     *
     * @param \PHPUnit\Framework\MockObject\MockObject $attributeSetMock
     * @param $setEntityTypeId
     */
    protected function setMockForValidation(
        \PHPUnit\Framework\MockObject\MockObject $attributeSetMock,
        $setEntityTypeId
    ) {
        $typeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $typeMock->expects($this->once())->method('getId')->willReturn(4);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->willReturn($typeMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn($setEntityTypeId);
    }
}
