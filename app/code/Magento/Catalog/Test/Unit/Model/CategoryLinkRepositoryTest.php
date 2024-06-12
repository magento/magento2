<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\CategoryLinkRepository
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryLinkRepositoryTest extends TestCase
{
    /**
     * @var CategoryLinkRepository
     */
    private $model;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var CategoryProductLinkInterface|MockObject
     */
    private $productLinkMock;

    /**
     * @var Product|MockObject
     */
    private $productResourceMock;

    /**
     * Initialize required data
     */
    protected function setUp(): void
    {
        $this->productResourceMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProductsIdsBySkus'])
            ->getMock();
        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->productLinkMock = $this->getMockForAbstractClass(CategoryProductLinkInterface::class);
        $this->model = new CategoryLinkRepository(
            $this->categoryRepositoryMock,
            $this->productRepositoryMock,
            $this->productResourceMock
        );
    }

    /**
     * Assign a product to the category
     *
     * @return void
     */
    public function testSave(): void
    {
        $categoryId = 42;
        $productId = 55;
        $productPosition = 1;
        $sku = 'testSku';
        $productPositions = [$productId => $productPosition];
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['getPostedProducts', 'setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->createMock(ProductModel::class);
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
     * Assign a product to the category with `CouldNotSaveException`
     *
     * @return void
     */
    public function testSaveWithCouldNotSaveException(): void
    {
        $categoryId = 42;
        $productId = 55;
        $productPosition = 1;
        $sku = 'testSku';
        $productPositions = [$productId => $productPosition];
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->createMock(ProductModel::class);
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

        $this->expectExceptionMessage('Could not save product "55" with position 1 to category 42');
        $this->expectException(CouldNotSaveException::class);
        $this->model->save($this->productLinkMock);
    }

    /**
     * Remove the product assignment from the category
     *
     * @return void
     */
    public function testDeleteByIds(): void
    {
        $categoryId = 42;
        $productSku = 'testSku';
        $productId = 55;
        $productPositions = [55 => 1];
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->createMock(ProductModel::class);
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
     * Delete the product assignment from the category with `CouldNotSaveException`
     *
     * @return void
     */
    public function testDeleteByIdsWithCouldNotSaveException(): void
    {
        $categoryId = 42;
        $productSku = 'testSku';
        $productId = 55;
        $productPositions = [55 => 1];
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->createMock(ProductModel::class);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $productMock->expects($this->exactly(2))->method('getId')->willReturn($productId);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with([]);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $categoryMock->expects($this->once())->method('save')->willThrowException(new \Exception());

        $this->expectExceptionMessage('Could not save product "55" with position 1 to category 42');
        $this->expectException(CouldNotSaveException::class);
        $this->model->deleteByIds($categoryId, $productSku);
    }

    /**
     * Delete the product assignment from the category with `InputException`
     *
     * @return void
     */
    public function testDeleteWithInputException(): void
    {
        $categoryId = 42;
        $productSku = 'testSku';
        $productId = 60;
        $productPositions = [55 => 1];
        $this->productLinkMock->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $this->productLinkMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->createMock(ProductModel::class);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($productMock);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $categoryMock->expects($this->never())->method('save');

        $this->expectExceptionMessage('The category doesn\'t contain the specified product.');
        $this->expectException(InputException::class);
        $this->assertTrue($this->model->delete($this->productLinkMock));
    }

    /**
     * Delete the product assignment from the category
     *
     * @return void
     */
    public function testDelete(): void
    {
        $categoryId = 42;
        $productSku = 'testSku';
        $productId = 55;
        $productPositions = [55 => 1];
        $this->productLinkMock->expects($this->once())->method('getCategoryId')->willReturn($categoryId);
        $this->productLinkMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->createMock(ProductModel::class);
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

    /**
     * Delete by products skus
     *
     * @return void
     */
    public function testDeleteBySkus(): void
    {
        $categoryId = 42;
        $productSkus = ['testSku', 'testSku1', 'testSku2', 'testSku3'];
        $productPositions = [55 => 1, 56 => 2, 57 => 3, 58 => 4];
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productResourceMock->expects($this->once())->method('getProductsIdsBySkus')
            ->willReturn(['testSku' => 55, 'testSku1' => 56, 'testSku2' => 57, 'testSku3' => 58]);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with([]);
        $categoryMock->expects($this->once())->method('save');

        $this->assertTrue($this->model->deleteBySkus($categoryId, $productSkus));
    }

    /**
     * Delete by products skus with `InputException`
     *
     * @return void
     */
    public function testDeleteBySkusWithInputException(): void
    {
        $categoryId = 42;
        $productSku = 'testSku';
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);

        $this->expectExceptionMessage('The category doesn\'t contain the specified products.');
        $this->expectException(InputException::class);
        $this->model->deleteBySkus($categoryId, [$productSku]);
    }

    /**
     * Delete by products skus with `CouldNotSaveException`
     *
     * @return void
     */
    public function testDeleteSkusIdsWithCouldNotSaveException(): void
    {
        $categoryId = 42;
        $productSku = 'testSku';
        $productId = 55;
        $productPositions = [55 => 1];
        $categoryMock = $this->getMockBuilder(Category::class)
            ->addMethods(['setPostedProducts'])
            ->onlyMethods(['getProductsPosition', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $this->productResourceMock->expects($this->once())->method('getProductsIdsBySkus')
            ->willReturn(['testSku' => $productId]);
        $categoryMock->expects($this->once())->method('getProductsPosition')->willReturn($productPositions);
        $categoryMock->expects($this->once())->method('setPostedProducts')->with([]);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $categoryMock->expects($this->once())->method('save')->willThrowException(new \Exception());

        $this->expectExceptionMessage('Could not save products "testSku" to category 42');
        $this->expectException(CouldNotSaveException::class);
        $this->assertTrue($this->model->deleteBySkus($categoryId, [$productSku]));
    }
}
