<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Media;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\AttributeManagement;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class AttributeManagementTest extends TestCase
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
     * @var MockObject
     */
    private $factoryMock;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    protected function setUp(): void
    {
        $this->factoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->storeId = 1;
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->storeId);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with(null)
            ->willReturn($storeMock);
        $this->model = new AttributeManagement(
            $this->factoryMock,
            $this->storeManagerMock
        );
    }

    public function testGetList()
    {
        $attributeSetName = 'Default Attribute Set';
        $expectedResult = [
            $this->getMockForAbstractClass(ProductAttributeInterface::class),
        ];
        $collectionMock = $this->createMock(Collection::class);
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
            ->willReturn($expectedResult);
        $this->factoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $this->assertEquals($expectedResult, $this->model->getList($attributeSetName));
    }
}
