<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;

class CategoryLinkRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\CategoryLinkRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkMock;

    protected function setUp()
    {
        $this->categoryRepositoryMock = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productLinkMock = $this->createMock(\Magento\Catalog\Api\Data\CategoryProductLinkInterface::class);
        $this->model = new \Magento\Catalog\Model\CategoryLinkRepository(
            $this->categoryRepositoryMock,
            $this->productRepositoryMock
        );
    }

    public function testSave()
    {
        $categoryId = 42;
        $productId = 55;
        $productPosition = 1;
        $sku = 'testSku';
        $productPositions = [$productId => $productPosition];
        $categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getPostedProducts', 'getProductsPosition', 'setPostedProducts', 'save']
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productLinkMock->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $this->productLinkMock->expects($this->once())->method('getSku')->willReturn($sku);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($sku)->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn([]);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->productLinkMock->expects($this->once())->method('getPosition')->willReturn($productPosition);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with($productPositions);
        $categoryMock->expects($this->once())->method('save');
        $this->assertTrue($this->model->save($this->productLinkMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save product "55" with position 1 to category 42
     */
    public function testSaveWithCouldNotSaveException()
    {
        $categoryId = 42;
        $productId = 55;
        $productPosition = 1;
        $sku = 'testSku';
        $productPositions = [$productId => $productPosition];
        $categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getProductsPosition', 'setPostedProducts', 'save', 'getId']
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productLinkMock->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $this->productLinkMock->expects($this->once())->method('getSku')->willReturn($sku);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($sku)->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn([]);
        $productMock->expects($this->exactly(2))->method('getId')->willReturn($productId);
        $this->productLinkMock->expects($this->exactly(2))->method('getPosition')->willReturn($productPosition);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with($productPositions);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $categoryMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->model->save($this->productLinkMock);
    }

    public function testDeleteByIds()
    {
        $categoryId = "42";
        $productSku = "testSku";
        $productId = 55;
        $productPositions = [55 => 1];
        $categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getProductsPosition', 'setPostedProducts', 'save', 'getId']
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with([]);
        $categoryMock->expects($this->once())->method('save');
        $this->assertTrue($this->model->deleteByIds($categoryId, $productSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save product "55" with position 1 to category 42
     */
    public function testDeleteByIdsWithCouldNotSaveException()
    {
        $categoryId = "42";
        $productSku = "testSku";
        $productId = 55;
        $productPositions = [55 => 1];
        $categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getProductsPosition', 'setPostedProducts', 'save', 'getId']
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $productMock->expects($this->exactly(2))->method('getId')->willReturn($productId);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with([]);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $categoryMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->model->deleteByIds($categoryId, $productSku);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The category doesn't contain the specified product.
     */
    public function testDeleteWithInputException()
    {
        $categoryId = "42";
        $productSku = "testSku";
        $productId = 60;
        $productPositions = [55 => 1];
        $this->productLinkMock->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $this->productLinkMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getProductsPosition', 'setPostedProducts', 'save', 'getId']
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);

        $categoryMock->expects($this->never())->method('save');
        $this->assertTrue($this->model->delete($this->productLinkMock));
    }

    public function testDelete()
    {
        $categoryId = "42";
        $productSku = "testSku";
        $productId = 55;
        $productPositions = [55 => 1];
        $this->productLinkMock->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $this->productLinkMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getProductsPosition', 'setPostedProducts', 'save', 'getId']
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with([]);
        $categoryMock->expects($this->once())->method('save');
        $this->assertTrue($this->model->delete($this->productLinkMock));
    }
}
