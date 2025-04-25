<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Link\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
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

    protected function setUp(): void
    {
        $linkCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['setLinkModel']
            )->getMock();
        $linkCollection->expects($this->any())->method('setLinkModel')->willReturnSelf();
        $linkCollectionFactory = $this->getMockBuilder(
            CollectionFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['create']
            )->getMock();
        $linkCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($linkCollection);
        $this->productCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['setLinkModel']
            )->getMock();
        $this->productCollection->expects($this->any())->method('setLinkModel')->willReturnSelf();
        $productCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['create']
            )->getMock();
        $productCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->productCollection);

        $this->resource = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(
                ['saveProductLinks', 'getAttributeTypeTable', 'getAttributesByType', 'getTable', 'getIdFieldName']
            )
            ->onlyMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->saveProductLinksMock = $this->getMockBuilder(SaveHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $this->resource
            ->expects($this->any())
            ->method('getTable')
            ->with($attributeTypeTable)
            ->willReturn($attributeTypeTable);
        $this->resource
            ->expects($this->any())
            ->method('getAttributeTypeTable')
            ->with($attributeType)
            ->willReturn($attributeTypeTable);
        $this->assertEquals($attributeTypeTable, $this->model->getAttributeTypeTable($attributeType));
    }

    public function testGetProductCollection()
    {
        $this->assertInstanceOf(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection::class,
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
        $this->resource
            ->expects($this->any())->method('getAttributesByType')
            ->with($typeId)
            ->willReturn($linkAttributes);
        $this->model->setData('link_type_id', $typeId);
        $this->assertEquals($linkAttributes, $this->model->getAttributes());
    }

    public function testSaveProductRelations()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saveProductLinksMock
            ->expects($this->once())
            ->method('execute')
            ->with(ProductInterface::class, $product);
        $this->model->saveProductRelations($product);
    }
}
