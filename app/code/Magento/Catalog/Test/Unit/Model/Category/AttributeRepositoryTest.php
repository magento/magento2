<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category\AttributeRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    protected function setUp()
    {
        $this->searchBuilderMock =
            $this->getMock(\Magento\Framework\Api\SearchCriteriaBuilder::class, [], [], '', false);
        $this->filterBuilderMock =
            $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        $this->attributeRepositoryMock =
            $this->getMock(\Magento\Eav\Api\AttributeRepositoryInterface::class, [], [], '', false);
        $this->searchResultMock =
            $this->getMock(
                \Magento\Framework\Api\SearchResultsInterface::class,
                [
                    'getItems',
                    'getSearchCriteria',
                    'getTotalCount',
                    'setItems',
                    'setSearchCriteria',
                    'setTotalCount',
                    '__wakeup',
                ],
                [],
                '',
                false);
        $this->eavConfigMock = $this->getMock(\Magento\Eav\Model\Config::class, [], [], '', false);
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
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
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
            $this->getMock(\Magento\Catalog\Api\Data\CategoryAttributeInterface::class, [], [], '', false);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($dataInterfaceMock);

        $this->model->get($attributeCode);
    }

    public function testGetCustomAttributesMetadata()
    {
        $filterMock = $this->getMock(\Magento\Framework\Api\Filter::class, [], [], '', false);
        $this->filterBuilderMock->expects($this->once())->method('setField')
            ->with('attribute_set_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with(
            3
        )->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);
        $this->searchBuilderMock->expects($this->once())->method('addFilters')->with([$filterMock])->willReturnSelf();
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $this->searchBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $itemMock = $this->getMock(\Magento\Framework\DataObject::class, [], [], '', false);
        $this->attributeRepositoryMock->expects($this->once())->method('getList')->with(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteriaMock
        )->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);
        $expected = [$itemMock];

        $this->assertEquals($expected, $this->model->getCustomAttributesMetadata(null));
    }
}
