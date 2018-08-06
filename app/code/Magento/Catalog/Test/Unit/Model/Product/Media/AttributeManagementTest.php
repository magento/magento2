<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Media;

use Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product\Media\AttributeManagement;

class AttributeManagementTest extends \PHPUnit\Framework\TestCase
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
        $this->factoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create']
        );
        $this->storeId = 1;
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
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
            $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeInterface::class),
        ];
        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class);
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
