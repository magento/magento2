<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductAttributeSearchResults;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModelProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product as PluginResourceModelProduct;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    /**
     * @var PluginResourceModelProduct
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Configurable|MockObject
     */
    private $configurableMock;

    /**
     * @var ActionInterface|MockObject
     */
    private $actionMock;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productAttributeRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilderMock;

    protected function setUp(): void
    {
        $this->configurableMock = $this->createMock(Configurable::class);
        $this->actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $this->productAttributeRepositoryMock = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['addFilters', 'create']
        );
        $this->filterBuilderMock = $this->createPartialMock(
            FilterBuilder::class,
            ['setField', 'setConditionType', 'setValue', 'create']
        );
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            PluginResourceModelProduct::class,
            [
                'configurable' => $this->configurableMock,
                'productIndexer' => $this->actionMock,
                'productAttributeRepository' => $this->productAttributeRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock
            ]
        );
    }

    public function testBeforeSaveConfigurable(): void
    {
        /** @var ResourceModelProduct|MockObject $subject */
        $subject = $this->createMock(ResourceModelProduct::class);
        /** @var ModelProduct|MockObject $object */
        $object = $this->createPartialMock(
            ModelProduct::class,
            [
                'getTypeId',
                'getTypeInstance',
                'getExtensionAttributes',
                'setData'
            ]
        );
        $type = $this->createPartialMock(
            Configurable::class,
            ['getSetAttributes']
        );
        $extensionAttributes = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getConfigurableProductOptions'])
            ->getMock();
        $option = $this->createPartialMock(
            ConfigurableAttribute::class,
            ['getAttributeId']
        );
        $extensionAttributes->expects($this->exactly(2))
            ->method('getConfigurableProductOptions')
            ->willReturn([$option]);
        $object->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('setValue')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('setConditionType')
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturnSelf();
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResultMockClass = $this->createPartialMock(
            ProductAttributeSearchResults::class,
            ['getItems']
        );
        $this->productAttributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResultMockClass);
        $optionAttribute = $this->createPartialMock(
            EavAttribute::class,
            ['getAttributeCode']
        );
        $searchResultMockClass->expects($this->once())
            ->method('getItems')
            ->willReturn([$optionAttribute]);
        $type->expects($this->once())
            ->method('getSetAttributes')
            ->with($object);
        $object->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(Configurable::TYPE_CODE));
        $object->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($type));
        $object->expects($this->once())
            ->method('setData');
        $option->expects($this->once())
            ->method('getAttributeId');
        $optionAttribute->expects($this->once())
            ->method('getAttributeCode');

        $this->model->beforeSave(
            $subject,
            $object
        );
    }

    public function testBeforeSaveSimple(): void
    {
        /** @var ResourceModelProduct|MockObject$subject */
        $subject = $this->createMock(ResourceModelProduct::class);
        /** @var ModelProduct|MockObject $object */
        $object = $this->createPartialMock(
            ModelProduct::class,
            [
                'getTypeId',
                'getTypeInstance'
            ]
        );
        $object->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(Type::TYPE_SIMPLE));
        $object->expects($this->never())
            ->method('getTypeInstance');

        $this->model->beforeSave(
            $subject,
            $object
        );
    }

    public function testAroundDelete(): void
    {
        $productId = '1';
        $parentConfigId = ['2'];
        /** @var ResourceModelProduct|MockObject $subject */
        $subject = $this->createMock(ResourceModelProduct::class);
        /** @var ModelProduct|MockObject $product */
        $product = $this->createPartialMock(
            ModelProduct::class,
            ['getId', 'delete']
        );
        $product->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $product->expects($this->once())
            ->method('delete')
            ->willReturn(true);
        $this->configurableMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn($parentConfigId);
        $this->actionMock->expects($this->once())
            ->method('executeList')
            ->with($parentConfigId);

        $return = $this->model->aroundDelete(
            $subject,
            /** @var ModelProduct|MockObject $prod */
            function (ModelProduct $prod) use ($subject) {
                $prod->delete();
                return $subject;
            },
            $product
        );

        $this->assertEquals($subject->getTypeId(), $return->getTypeId());
    }
}
