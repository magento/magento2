<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

use \Magento\Catalog\Model\Product\Link;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Link
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\Product\Link\SaveHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saveProductLinksMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCollection;

    protected function setUp()
    {
        $linkCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Collection::class
        )->disableOriginalConstructor()->setMethods(
            ['setLinkModel']
        )->getMock();
        $linkCollection->expects($this->any())->method('setLinkModel')->will($this->returnSelf());
        $linkCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $linkCollectionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($linkCollection));
        $this->productCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection::class
        )->disableOriginalConstructor()->setMethods(
            ['setLinkModel']
        )->getMock();
        $this->productCollection->expects($this->any())->method('setLinkModel')->will($this->returnSelf());
        $productCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $productCollectionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->productCollection));

        $this->resource = $this->getMock(
            \Magento\Framework\Model\ResourceModel\AbstractResource::class,
            [
                'saveProductLinks',
                'getAttributeTypeTable',
                'getAttributesByType',
                'getTable',
                'getConnection',
                '_construct',
                'getIdFieldName',
            ]
        );

        $this->saveProductLinksMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Link\SaveHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Link::class,
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
            ->will($this->returnValue($attributeTypeTable));
        $this->resource
            ->expects($this->any())
            ->method('getAttributeTypeTable')
            ->with($attributeType)
            ->will($this->returnValue($attributeTypeTable));
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
            \Magento\Catalog\Model\ResourceModel\Product\Link\Collection::class,
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
            ->will($this->returnValue($linkAttributes));
        $this->model->setData('link_type_id', $typeId);
        $this->assertEquals($linkAttributes, $this->model->getAttributes());
    }

    public function testSaveProductRelations()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saveProductLinksMock
            ->expects($this->once())
            ->method('execute')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class, $product);
        $this->model->saveProductRelations($product);
    }
}
