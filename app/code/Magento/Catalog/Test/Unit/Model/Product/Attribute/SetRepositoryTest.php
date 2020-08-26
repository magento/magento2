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
use Magento\Catalog\Model\Product\Attribute\SetRepository;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Api\Data\AttributeSetSearchResultsInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetRepositoryTest extends TestCase
{
    /**
     * @var SetRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $attrSetRepositoryMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject
     */
    protected $eavConfig;

    protected function setUp(): void
    {
        $this->attrSetRepositoryMock = $this->getMockForAbstractClass(AttributeSetRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->eavConfig = $this->createMock(Config::class);

        $this->model = new SetRepository(
            $this->attrSetRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock,
            $this->eavConfig
        );
    }

    public function testSave()
    {
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 4);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('save')
            ->with($attributeSetMock)
            ->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->save($attributeSetMock));
    }

    public function testSaveNonProductAttributeSet()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('Provided Attribute set non product Attribute set.');
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 3);
        $this->attrSetRepositoryMock->expects($this->never())->method('save');
        $this->model->save($attributeSetMock);
    }

    public function testGet()
    {
        $attributeSetId = 1;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 4);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->get($attributeSetId));
    }

    public function testGetNonProductAttributeSet()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('Provided Attribute set non product Attribute set.');
        $attributeSetId = 1;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 3);

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->get($attributeSetId));
    }

    public function testDelete()
    {
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 4);
        $this->attrSetRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($attributeSetMock)
            ->willReturn(true);
        $this->assertTrue($this->model->delete($attributeSetMock));
    }

    public function testDeleteNonProductAttributeSet()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('Provided Attribute set non product Attribute set.');
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setMockForValidation($attributeSetMock, 3);
        $this->attrSetRepositoryMock->expects($this->never())
            ->method('delete');
        $this->assertTrue($this->model->delete($attributeSetMock));
    }

    public function testDeleteById()
    {
        $attributeSetId = 1;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
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
        $searchResultMock = $this->getMockForAbstractClass(AttributeSetSearchResultsInterface::class);

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn(1);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn(2);

        $filterMock = $this->createMock(Filter::class);

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('entity_type_code')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE)
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
            ->willReturn($this->getMockForAbstractClass(SearchCriteriaInterface::class));

        $this->attrSetRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);
        $this->assertEquals($searchResultMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Set mock for attribute set validation
     *
     * @param MockObject $attributeSetMock
     * @param $setEntityTypeId
     */
    protected function setMockForValidation(
        MockObject $attributeSetMock,
        $setEntityTypeId
    ) {
        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->once())->method('getId')->willReturn(4);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Product::ENTITY)
            ->willReturn($typeMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn($setEntityTypeId);
    }
}
