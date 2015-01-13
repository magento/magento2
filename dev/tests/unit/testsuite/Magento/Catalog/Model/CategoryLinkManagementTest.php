<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;


class CategoryLinkManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\CategoryLinkManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkBuilderMock;

    protected function setUp()
    {
        $this->categoryRepositoryMock = $this->getMock('\Magento\Catalog\Model\CategoryRepository', [], [], '', false);
        $this->productLinkBuilderMock = $this->getMock(
            '\Magento\Catalog\Api\Data\CategoryProductLinkDataBuilder',
            ['populateWithArray', 'create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Catalog\Model\CategoryLinkManagement(
            $this->categoryRepositoryMock,
            $this->productLinkBuilderMock
        );
    }

    public function testGetAssignedProducts()
    {
        $categoryId = 42;
        $productId = 55;
        $productsPosition = [$productId => 25];
        $productSku = 'testSku';
        $expectedValue = 'testComplete';
        $categoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            '',
            false
        );
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $items = [$productId => $productMock];
        $productLinkArray = [
            'sku' => $productSku,
            'position' => 25,
            'category_id' => $categoryId,
        ];
        $productsMock = $this->getMock('\Magento\Framework\Data\Collection\Db', [], [], '', false);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productsPosition);
        $categoryMock->expects($this->once())->method('getProductCollection')->willReturn($productsMock);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $productsMock->expects($this->once())->method('getItems')->willReturn($items);
        $this->productLinkBuilderMock->expects($this->once())->method('populateWithArray')->with($productLinkArray)
            ->willReturnSelf();
        $this->productLinkBuilderMock->expects($this->once())->method('create')->willReturn($expectedValue);
        $this->assertEquals([$expectedValue], $this->model->getAssignedProducts($categoryId));
    }
}
