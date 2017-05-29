<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Repository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavAttributeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultMock;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionManagementMock;

    protected function setUp()
    {
        $this->attributeResourceMock =
            $this->getMock(\Magento\Catalog\Model\ResourceModel\Attribute::class, [], [], '', false);
        $this->productHelperMock =
            $this->getMock(\Magento\Catalog\Helper\Product::class, [], [], '', false);
        $this->filterManagerMock =
            $this->getMock(\Magento\Framework\Filter\FilterManager::class, [], [], '', false);
        $this->eavAttributeRepositoryMock =
            $this->getMock(\Magento\Eav\Api\AttributeRepositoryInterface::class, [], [], '', false);
        $this->eavConfigMock = $this->getMock(\Magento\Eav\Model\Config::class, [], [], '', false);
        $this->eavConfigMock->expects($this->any())->method('getEntityType')
            ->willReturn(new \Magento\Framework\DataObject(['default_attribute_set_id' => 4]));
        $this->validatorFactoryMock = $this->getMock(
            \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory::class,
            ['create'],
            [],
            '',
            false);
        $this->searchCriteriaBuilderMock =
            $this->getMock(\Magento\Framework\Api\SearchCriteriaBuilder::class, [], [], '', false);
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
        $this->optionManagementMock =
            $this->getMock(\Magento\Catalog\Api\ProductAttributeOptionManagementInterface::class, [], [], '', false);

        $this->model = new Repository(
            $this->attributeResourceMock,
            $this->productHelperMock,
            $this->filterManagerMock,
            $this->eavAttributeRepositoryMock,
            $this->eavConfigMock,
            $this->validatorFactoryMock,
            $this->searchCriteriaBuilderMock,
            $this->optionManagementMock
        );
    }

    public function testGet()
    {
        $attributeCode = 'some attribute code';
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            );
        $this->model->get($attributeCode);
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $searchCriteriaMock
            );

        $this->model->getList($searchCriteriaMock);
    }

    public function testDelete()
    {
        $attributeMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);
        $this->attributeResourceMock->expects($this->once())->method('delete')->with($attributeMock);

        $this->assertEquals(true, $this->model->delete($attributeMock));
    }

    public function testDeleteById()
    {
        $attributeCode = 'some attribute code';
        $attributeMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            )->willReturn($attributeMock);
        $this->attributeResourceMock->expects($this->once())->method('delete')->with($attributeMock);

        $this->assertEquals(true, $this->model->deleteById($attributeCode));
    }

    public function testGetCustomAttributesMetadata()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $itemMock = $this->getMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $searchCriteriaMock
            )->willReturn($this->searchResultMock);
        $this->searchResultMock->expects($this->once())->method('getItems')->willReturn([$itemMock]);
        $expected = [$itemMock];

        $this->assertEquals($expected, $this->model->getCustomAttributesMetadata());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attribute_code = test attribute code
     */
    public function testSaveNoSuchEntityException()
    {
        $attributeMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);
        $existingModelMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [],
            [],
            '',
            false
        );
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn('12');
        $attributeCode = 'test attribute code';
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            )
            ->willReturn($existingModelMock);
        $existingModelMock->expects($this->once())->method('getAttributeId')->willReturn(null);
        $existingModelMock->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);

        $this->model->save($attributeMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage frontend_label is a required field.
     */
    public function testSaveInputExceptionRequiredField()
    {
        $attributeMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getFrontendLabels', 'getDefaultFrontendLabel', '__wakeup', 'getAttributeId', 'setAttributeId'],
            [],
            '',
            false
        );
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $attributeMock->expects($this->once())->method('getFrontendLabels')->willReturn(null);
        $attributeMock->expects($this->once())->method('getDefaultFrontendLabel')->willReturn(null);

        $this->model->save($attributeMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the frontend_label field.
     */
    public function testSaveInputExceptionInvalidFieldValue()
    {
        $attributeMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getFrontendLabels', 'getDefaultFrontendLabel', 'getAttributeId', '__wakeup', 'setAttributeId'],
            [],
            '',
            false
        );
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(null);
        $attributeMock->expects($this->once())->method('setAttributeId')->with(null)->willReturnSelf();
        $labelMock = $this->getMock(\Magento\Eav\Api\Data\AttributeFrontendLabelInterface::class, [], [], '', false);
        $attributeMock->expects($this->exactly(4))->method('getFrontendLabels')->willReturn([$labelMock]);
        $attributeMock->expects($this->exactly(2))->method('getDefaultFrontendLabel')->willReturn('test');
        $labelMock->expects($this->once())->method('getStoreId')->willReturn(0);
        $labelMock->expects($this->once())->method('getLabel')->willReturn(null);

        $this->model->save($attributeMock);
    }

    public function testSaveDoesNotSaveAttributeOptionsIfOptionsAreAbsentInPayload()
    {
        $attributeId = 1;
        $attributeCode = 'existing_attribute_code';
        $attributeMock = $this->getMock(Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);

        $existingModelMock = $this->getMock(Attribute::class, [], [], '', false);
        $existingModelMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $existingModelMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);

        $this->eavAttributeRepositoryMock->expects($this->any())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($existingModelMock);

        // Attribute code must not be changed after attribute creation
        $attributeMock->expects($this->once())->method('setAttributeCode')->with($attributeCode);
        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);
        $this->optionManagementMock->expects($this->never())->method('add');

        $this->model->save($attributeMock);
    }

    public function testSaveSavesDefaultFrontendLabelIfItIsPresentInPayload()
    {
        $labelMock = $this->getMock(AttributeFrontendLabelInterface::class);
        $labelMock->expects($this->any())->method('getStoreId')->willReturn(1);
        $labelMock->expects($this->any())->method('getLabel')->willReturn('Store Scope Label');

        $attributeId = 1;
        $attributeCode = 'existing_attribute_code';
        $attributeMock = $this->getMock(Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);
        $attributeMock->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('Default Label');
        $attributeMock->expects($this->any())->method('getFrontendLabels')->willReturn([$labelMock]);
        $attributeMock->expects($this->any())->method('getOptions')->willReturn([]);

        $existingModelMock = $this->getMock(Attribute::class, [], [], '', false);
        $existingModelMock->expects($this->any())->method('getAttributeId')->willReturn($attributeId);
        $existingModelMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);

        $this->eavAttributeRepositoryMock->expects($this->any())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($existingModelMock);

        $attributeMock->expects($this->once())
            ->method('setDefaultFrontendLabel')
            ->with(
                [
                    0 => 'Default Label',
                    1 => 'Store Scope Label'
                ]
            );
        $this->attributeResourceMock->expects($this->once())->method('save')->with($attributeMock);

        $this->model->save($attributeMock);
    }
}
