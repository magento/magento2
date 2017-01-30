<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;


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
    protected $productLinkFactoryMock;

    protected function setUp()
    {
        $this->categoryRepositoryMock = $this->getMock('\Magento\Catalog\Model\CategoryRepository', [], [], '', false);
        $this->productLinkFactoryMock = $this->getMock(
            '\Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Catalog\Model\CategoryLinkManagement(
            $this->categoryRepositoryMock,
            $this->productLinkFactoryMock
        );
    }

    public function testGetAssignedProducts()
    {
        $categoryId = 42;
        $productId = 55;
        $position = 25;
        $productSku = 'testSku';
        $categoryProductLinkMock = $this->getMock('\Magento\Catalog\Api\Data\CategoryProductLinkInterface');
        $categoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            '',
            false
        );
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $productMock->expects($this->once())->method('getData')->with('cat_index_position')->willReturn($position);
        $items = [$productId => $productMock];
        $productsMock = $this->getMock('Magento\Catalog\Model\ResourceModel\Product\Collection', [], [], '', false);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $categoryMock->expects($this->once())->method('getProductCollection')->willReturn($productsMock);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $productsMock->expects($this->once())->method('addFieldToSelect')->with('position')->willReturnSelf();
        $productsMock->expects($this->once())->method('getItems')->willReturn($items);
        $this->productLinkFactoryMock->expects($this->once())->method('create')->willReturn($categoryProductLinkMock);
        $categoryProductLinkMock->expects($this->once())
            ->method('setSku')
            ->with($productSku)
            ->willReturnSelf();
        $categoryProductLinkMock->expects($this->once())
            ->method('setPosition')
            ->with($position)
            ->willReturnSelf();
        $categoryProductLinkMock->expects($this->once())
            ->method('setCategoryId')
            ->with($categoryId)
            ->willReturnSelf();
        $this->assertEquals([$categoryProductLinkMock], $this->model->getAssignedProducts($categoryId));
    }
}
