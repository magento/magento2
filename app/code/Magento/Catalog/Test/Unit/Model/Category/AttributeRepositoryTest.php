<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category\AttributeRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeRepository
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchResultMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavConfigMock;

    protected function setUp(): void
    {
        $this->searchBuilderMock =
            $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->filterBuilderMock =
            $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->attributeRepositoryMock =
            $this->createMock(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        $this->searchResultMock =
            $this->createPartialMock(\Magento\Framework\Api\SearchResultsInterface::class, [
                    'getItems',
                    'getSearchCriteria',
                    'getTotalCount',
                    'setItems',
                    'setSearchCriteria',
                    'setTotalCount',
                    '__wakeup',
                ]);
        $this->eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->eavConfigMock->expects($this->any())->method('getEntityType')
            ->willReturn(new \Magento\Framework\DataObject(['default_attribute_set_id' => 3]));
        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Catalog\Model\Category\AttributeRepository::class,
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
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE, $searchCriteriaMock)
            ->willReturn($this->searchResultMock);

        $this->model->getList($searchCriteriaMock);
    }

    public function testGet()
    {
        $attributeCode = 'some Attribute Code';
        $dataInterfaceMock =
            $this->createMock(\Magento\Catalog\Api\Data\CategoryAttributeInterface::class);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($dataInterfaceMock);

        $this->model->get($attributeCode);
    }

    public function testGetCustomAttributesMetadata()
    {
        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilderMock->expects($this->once())->method('setField')
            ->with('attribute_set_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with(
            3
        )->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);
        $this->searchBuilderMock->expects($this->once())->method('addFilters')->with([$filterMock])->willReturnSelf();
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $itemMock = $this->createMock(\Magento\Framework\DataObject::class);
        $this->attributeRepositoryMock->expects($this->once())->method('getList')->with(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteriaMock
        )->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);
        $expected = [$itemMock];

        $this->assertEquals($expected, $this->model->getCustomAttributesMetadata(null));
    }
}
