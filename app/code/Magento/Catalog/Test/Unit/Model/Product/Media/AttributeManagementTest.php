<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Media;

use \Magento\Catalog\Model\Product\Media\AttributeManagement;

use Magento\Catalog\Model\Product;

class AttributeManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeManagement
     */
    private $model;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $this->factoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->storeId = 1;
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($this->storeId));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->will($this->returnValue($storeMock));
        $this->model = new AttributeManagement(
            $this->factoryMock,
            $this->storeManagerMock
        );
    }

    public function testGetList()
    {
        $attributeSetName = 'Default Attribute Set';
        $expectedResult = [
            $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeInterface::class),
        ];
        $collectionMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class,
            [],
            [],
            '',
            false
        );
        $collectionMock->expects($this->once())
            ->method('setAttributeSetFilterBySetName')
            ->with($attributeSetName, Product::ENTITY);
        $collectionMock->expects($this->once())
            ->method('setFrontendInputTypeFilter')
            ->with('media_image');
        $collectionMock->expects($this->once())
            ->method('addStoreLabel')
            ->with($this->storeId);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($expectedResult));
        $this->factoryMock->expects($this->once())->method('create')->will($this->returnValue($collectionMock));

        $this->assertEquals($expectedResult, $this->model->getList($attributeSetName));
    }
}
