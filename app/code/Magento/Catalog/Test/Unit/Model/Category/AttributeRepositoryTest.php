<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Model\Category\AttributeRepository;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeRepositoryTest extends TestCase
{
    /**
     * @var AttributeRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $searchBuilderMock;

    /**
     * @var MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject
     */
    protected $attributeRepositoryMock;

    /**
     * @var MockObject
     */
    protected $searchResultMock;

    /**
     * @var Config|MockObject
     */
    protected $eavConfigMock;

    protected function setUp(): void
    {
        $this->searchBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->attributeRepositoryMock = $this->getMockForAbstractClass(AttributeRepositoryInterface::class);
        $this->searchResultMock = $this->getMockBuilder(SearchResultsInterface::class)
            ->onlyMethods(
                ['getItems', 'getSearchCriteria', 'getTotalCount', 'setItems', 'setSearchCriteria', 'setTotalCount']
            )
            ->getMockForAbstractClass();
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->eavConfigMock->expects($this->any())->method('getEntityType')
            ->willReturn(new DataObject(['default_attribute_set_id' => 3]));
        $this->model = (new ObjectManager($this))->getObject(
            AttributeRepository::class,
            [
                'searchCriteriaBuilder' => $this->searchBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'eavAttributeRepository' => $this->attributeRepositoryMock,
                'eavConfig' => $this->eavConfigMock,
            ]
        );
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with(CategoryAttributeInterface::ENTITY_TYPE_CODE, $searchCriteriaMock)
            ->willReturn($this->searchResultMock);

        $this->model->getList($searchCriteriaMock);
    }

    public function testGet()
    {
        $attributeCode = 'some Attribute Code';
        $dataInterfaceMock =
            $this->getMockForAbstractClass(CategoryAttributeInterface::class);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(CategoryAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($dataInterfaceMock);

        $this->model->get($attributeCode);
    }

    public function testGetCustomAttributesMetadata()
    {
        $filterMock = $this->createMock(Filter::class);
        $this->filterBuilderMock->expects($this->once())->method('setField')
            ->with('attribute_set_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with(3)->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);
        $this->searchBuilderMock->expects($this->once())->method('addFilters')->with([$filterMock])->willReturnSelf();
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $itemMock = $this->createMock(DataObject::class);
        $this->attributeRepositoryMock->expects($this->once())->method('getList')->with(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteriaMock
        )->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);
        $expected = [$itemMock];

        $this->assertEquals($expected, $this->model->getCustomAttributesMetadata(null));
    }
}
