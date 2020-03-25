<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var Configurable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurableMock;

    /**
     * @var ActionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionMock;

    /**
     * @var \Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product
     */
    private $model;
    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $prdAttributeRepository;
    /**
     * @var SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilder;
    /**
     * @var FilterBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterBuilder;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configurableMock = $this->createMock(Configurable::class);
        $this->actionMock = $this->createMock(ActionInterface::class);
//        $this->prdAttributeRepository = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->prdAttributeRepository = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['addFilters', 'create']
        );
        $this->filterBuilder = $this->createPartialMock(
            FilterBuilder::class,
            ['setField', 'setConditionType', 'setValue', 'create']
        );

        $this->model = $this->objectManager->getObject(
            \Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product::class,
            [
                'configurable' => $this->configurableMock,
                'productIndexer' => $this->actionMock,
                'productAttributeRepository' => $this->prdAttributeRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder
            ]
        );
    }

    public function testBeforeSaveConfigurable()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getTypeId',
                'getTypeInstance',
                'getExtensionAttributes',
                'setData'
            ]
        );
        $type = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            ['getSetAttributes']
        );

        $extensionAttributes = $this->createPartialMock(
            \Magento\Framework\Api\ExtensionAttributesInterface::class,
            ['getConfigurableProductOptions']
        );
        $option = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class,
            ['getAttributeId']
        );
        $extensionAttributes->expects($this->exactly(2))->method('getConfigurableProductOptions')
            ->willReturn([$option]);
        $object->expects($this->once())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->filterBuilder->expects($this->atLeastOnce())->method('setField')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setConditionType')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('create')->willReturnSelf();
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $searchResultMockClass = $this->createPartialMock(
            \Magento\Catalog\Model\ProductAttributeSearchResults::class,
            ['getItems']
        );
        $this->prdAttributeRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResultMockClass);
        $optionAttribute = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getAttributeCode']
        );
        $searchResultMockClass->expects($this->once())->method('getItems')->willReturn([$optionAttribute]);
        $type->expects($this->once())->method('getSetAttributes')->with($object);

        $object->expects($this->once())->method('getTypeId')->will($this->returnValue(Configurable::TYPE_CODE));
        $object->expects($this->once())->method('getTypeInstance')->will($this->returnValue($type));
        $object->expects($this->once())->method('setData');
        $option->expects($this->once())->method('getAttributeId');
        $optionAttribute->expects($this->once())->method('getAttributeCode');

        $this->model->beforeSave(
            $subject,
            $object
        );
    }

    public function testBeforeSaveSimple()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeId', 'getTypeInstance']);
        $object->expects($this->once())->method('getTypeId')->will($this->returnValue(Type::TYPE_SIMPLE));
        $object->expects($this->never())->method('getTypeInstance');

        $this->model->beforeSave(
            $subject,
            $object
        );
    }

    public function testAroundDelete()
    {
        $productId = '1';
        $parentConfigId = ['2'];
        /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', 'delete']
        );
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('delete')->willReturn(true);
        $this->configurableMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn($parentConfigId);
        $this->actionMock->expects($this->once())->method('executeList')->with($parentConfigId);

        $return = $this->model->aroundDelete(
            $subject,
            /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $prod */
            function (\Magento\Catalog\Model\Product $prod) use ($subject) {
                $prod->delete();
                return $subject;
            },
            $product
        );

        $this->assertEquals($subject->getTypeId(), $return->getTypeId());
    }
}
