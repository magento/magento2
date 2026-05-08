<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Link\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Link
     */
    protected $model;

    /**
     * @var AbstractResource|MockObject
     */
    protected $resource;

    /**
     * @var SaveHandler|MockObject
     */
    protected $saveProductLinksMock;

    /**
     * @var MockObject
     */
    protected $productCollection;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setUp(): void
    {
        $linkCollection = $this->createPartialMock(Collection::class, ['setLinkModel']);
        $linkCollection->expects($this->any())->method('setLinkModel')->willReturnSelf();
        $linkCollectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $linkCollectionFactory->method('create')->willReturn($linkCollection);
        $this->productCollection = $this->createPartialMock(
            ProductCollection::class,
            ['setLinkModel']
        );
        $this->productCollection->expects($this->any())->method('setLinkModel')->willReturnSelf();
        $productCollectionFactory = $this->createPartialMock(
            ProductCollectionFactory::class,
            ['create']
        );
        $productCollectionFactory->method('create')->willReturn($this->productCollection);

        $this->resource = $this->createPartialMockWithReflection(
            AbstractResource::class,
            [
                'getIdFieldName', 'getConnection', 'getMainTable', 'getTable', 'getAttributeTypeTable',
                'getAttributesByType', 'setTable', 'setAttributeTypeTable', 'setAttributesByType', '_construct'
            ]
        );
        $resourceData = [];
        $this->resource->method('getIdFieldName')->willReturn('link_id');
        $this->resource->method('getConnection')->willReturn(null);
        $this->resource->method('getMainTable')->willReturn('catalog_product_link');
        $this->resource->method('getTable')->willReturnCallback(function ($table = null) use (&$resourceData) {
            return $resourceData['table'] ?? $table;
        });
        $this->resource->method('setTable')->willReturnCallback(function ($table) use (&$resourceData) {
            $resourceData['table'] = $table;
        });
        $this->resource->method('getAttributeTypeTable')->willReturnCallback(function ($type) use (&$resourceData) {
            return $resourceData['attributeTypeTable'] ?? null;
        });
        $this->resource->method('setAttributeTypeTable')->willReturnCallback(function ($table) use (&$resourceData) {
            $resourceData['attributeTypeTable'] = $table;
        });
        $this->resource->method('getAttributesByType')->willReturnCallback(function () use (&$resourceData) {
            return $resourceData['attributesByType'] ?? [];
        });
        $this->resource->method('setAttributesByType')->willReturnCallback(function ($attrs) use (&$resourceData) {
            $resourceData['attributesByType'] = $attrs;
        });
        $this->resource->method('_construct')->willReturn(null);

        $this->saveProductLinksMock = $this->createMock(SaveHandler::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Link::class,
            [
                'linkCollectionFactory' => $linkCollectionFactory,
                'productCollectionFactory' => $productCollectionFactory,
                'resource' => $this->resource,
                'saveProductLinks' => $this->saveProductLinksMock
            ]
        );
    }

    public function testUseRelatedLinks()
    {
        $this->model->useRelatedLinks();
        $this->assertEquals(Link::LINK_TYPE_RELATED, $this->model->getData('link_type_id'));
    }

    public function testUseUpSellLinks()
    {
        $this->model->useUpSellLinks();
        $this->assertEquals(Link::LINK_TYPE_UPSELL, $this->model->getData('link_type_id'));
    }

    public function testUseCrossSellLinks()
    {
        $this->model->useCrossSellLinks();
        $this->assertEquals(Link::LINK_TYPE_CROSSSELL, $this->model->getData('link_type_id'));
    }

    public function testGetAttributeTypeTable()
    {
        $prefix = 'catalog_product_link_attribute_';
        $attributeType = 'int';
        $attributeTypeTable = $prefix . $attributeType;
        $this->resource->setTable($attributeTypeTable);
        $this->resource->setAttributeTypeTable($attributeTypeTable);
        $this->assertEquals($attributeTypeTable, $this->model->getAttributeTypeTable($attributeType));
    }

    public function testGetProductCollection()
    {
        $this->assertInstanceOf(
            ProductCollection::class,
            $this->model->getProductCollection()
        );
    }

    public function testGetLinkCollection()
    {
        $this->assertInstanceOf(
            Collection::class,
            $this->model->getLinkCollection()
        );
    }

    public function testGetAttributes()
    {
        $typeId = 1;
        $linkAttributes = ['link_type_id' => 1, 'product_link_attribute_code' => 1, 'data_type' => 'int', 'id' => 1];
        $this->resource->setAttributesByType($linkAttributes);
        $this->model->setData('link_type_id', $typeId);
        $this->assertEquals($linkAttributes, $this->model->getAttributes());
    }

    public function testSaveProductRelations()
    {
        $product = $this->createMock(Product::class);
        $this->saveProductLinksMock
            ->expects($this->once())
            ->method('execute')
            ->with(ProductInterface::class, $product);
        $this->model->saveProductRelations($product);
    }
}
