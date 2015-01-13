<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Link
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Resource\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    protected function setUp()
    {
        $linkCollection = $this->getMockBuilder(
            'Magento\Catalog\Model\Resource\Product\Link\Collection'
        )->disableOriginalConstructor()->setMethods(
            ['setLinkModel']
        )->getMock();
        $linkCollection->expects($this->any())->method('setLinkModel')->will($this->returnSelf());
        $linkCollectionFactory = $this->getMockBuilder(
            'Magento\Catalog\Model\Resource\Product\Link\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $linkCollectionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($linkCollection));
        $productCollection = $this->getMockBuilder(
            'Magento\Catalog\Model\Resource\Product\Link\Product\Collection'
        )->disableOriginalConstructor()->setMethods(
            ['setLinkModel']
        )->getMock();
        $productCollection->expects($this->any())->method('setLinkModel')->will($this->returnSelf());
        $productCollectionFactory = $this->getMockBuilder(
            'Magento\Catalog\Model\Resource\Product\Link\Product\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $productCollectionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($productCollection));

        $this->resource = $this->getMock(
            'Magento\Framework\Model\Resource\AbstractResource',
            [
                'saveProductLinks',
                'getAttributeTypeTable',
                'getAttributesByType',
                'getTable',
                '_getWriteAdapter',
                '_getReadAdapter',
                '_construct',
                'getIdFieldName'
            ]
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Product\Link',
            ['linkCollectionFactory' => $linkCollectionFactory, 'productCollectionFactory' => $productCollectionFactory,
                'resource' => $this->resource]
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
            'Magento\Catalog\Model\Resource\Product\Link\Product\Collection',
            $this->model->getProductCollection()
        );
    }

    public function testGetLinkCollection()
    {
        $this->assertInstanceOf(
            'Magento\Catalog\Model\Resource\Product\Link\Collection',
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
        $data = [1];
        $typeId = 1;
        $this->model->setData('link_type_id', $typeId);
        $product = $this->getMockBuilder(
            'Magento\Catalog\Model\Product'
        )->disableOriginalConstructor()->setMethods(
            ['getRelatedLinkData', 'getUpSellLinkData', 'getCrossSellLinkData', '__wakeup']
        )->getMock();
        $product->expects($this->any())->method('getRelatedLinkData')->will($this->returnValue($data));
        $product->expects($this->any())->method('getUpSellLinkData')->will($this->returnValue($data));
        $product->expects($this->any())->method('getCrossSellLinkData')->will($this->returnValue($data));
        $map = [
            [$product, $data, Link::LINK_TYPE_RELATED, $this->resource],
            [$product, $data, Link::LINK_TYPE_UPSELL, $this->resource],
            [$product, $data, Link::LINK_TYPE_CROSSSELL, $this->resource],
        ];
        $this->resource->expects($this->any())->method('saveProductLinks')->will($this->returnValueMap($map));
        $this->model->saveProductRelations($product);
    }
}
