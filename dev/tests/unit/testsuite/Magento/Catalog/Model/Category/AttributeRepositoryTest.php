<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Category;

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
    protected $metadataConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultMock;

    protected function setUp()
    {
        $this->searchBuilderMock =
            $this->getMock('Magento\Framework\Api\SearchCriteriaDataBuilder', [], [], '', false);
        $this->filterBuilderMock =
            $this->getMock('Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->attributeRepositoryMock =
            $this->getMock('Magento\Eav\Api\AttributeRepositoryInterface', [], [], '', false);
        $this->metadataConfigMock =
            $this->getMock('Magento\Framework\Api\Config\MetadataConfig', [], [], '', false);
        $this->searchResultMock =
            $this->getMock(
                'Magento\Framework\Api\SearchResultsInterface',
                [
                    'getItems',
                    'getSearchCriteria',
                    'getTotalCount',
                    '__wakeup'
                ],
                [],
                '',
                false);
        $this->model = new AttributeRepository(
            $this->metadataConfigMock,
            $this->searchBuilderMock,
            $this->filterBuilderMock,
            $this->attributeRepositoryMock
        );
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock('Magento\Framework\Api\SearchCriteria', [], [], '', false);
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
            $this->getMock('Magento\Catalog\Api\Data\CategoryAttributeInterface', [], [], '', false);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($dataInterfaceMock);

        $this->model->get($attributeCode);
    }

    public function testGetCustomAttributesMetadata()
    {
        $filterMock = $this->getMock('Magento\Framework\Service\V1\Data\Filter', [], [], '', false);
        $this->filterBuilderMock->expects($this->once())->method('setField')
            ->with('attribute_set_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::DEFAULT_ATTRIBUTE_SET_ID
        )->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);
        $this->searchBuilderMock->expects($this->once())->method('addFilter')->with([$filterMock])->willReturnSelf();
        $searchCriteriaMock = $this->getMock('Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $this->searchBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $itemMock = $this->getMock('Magento\Framework\Object', [], [], '', false);
        $this->attributeRepositoryMock->expects($this->once())->method('getList')->with(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteriaMock
        )->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);
        $this->metadataConfigMock->expects($this->once())
            ->method('getCustomAttributesMetadata')->with(null)->willReturn(['attribute']);
        $expected = array_merge([$itemMock], ['attribute']);

        $this->assertEquals($expected, $this->model->getCustomAttributesMetadata(null));
    }
}
