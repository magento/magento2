<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewriteGraphQl\Test\Unit\Model\DataProvider\UrlRewrite;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree as CategoryTreeDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\CatalogUrlRewriteGraphQl\Model\DataProvider\UrlRewrite\CatalogTreeDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogTreeDataProviderTest extends TestCase
{
    /**
     * @var CategoryTreeDataProvider|MockObject
     */
    private $categoryTreeMock;

    /**
     * @var ExtractDataFromCategoryTree|MockObject
     */
    private $extractDataFromCategoryTreeMock;

    /**
     * @var CategoryRepository|MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var CatalogTreeDataProvider
     */
    private $model;

    protected function setUp(): void
    {
        $this->categoryTreeMock = $this->createMock(CategoryTreeDataProvider::class);
        $this->extractDataFromCategoryTreeMock = $this->createMock(ExtractDataFromCategoryTree::class);
        $this->categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $this->resolveInfoMock = $this->createStub(ResolveInfo::class);

        $this->model = new CatalogTreeDataProvider(
            $this->categoryTreeMock,
            $this->extractDataFromCategoryTreeMock,
            $this->categoryRepositoryMock
        );
    }

    /**
     * A disabled category must not be resolvable through the GraphQL route query.
     */
    public function testGetDataThrowsForDisabledCategory(): void
    {
        $categoryMock = $this->createStub(Category::class);
        $categoryMock->method('getIsActive')->willReturn(false);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with(5, 1)->willReturn($categoryMock);

        $this->categoryTreeMock->expects($this->never())->method('getTreeCollection');
        $this->extractDataFromCategoryTreeMock->expects($this->never())->method('buildTree');

        $this->expectException(GraphQlNoSuchEntityException::class);

        $this->model->getData('category', 5, $this->resolveInfoMock, 1);
    }

    /**
     * A missing category propagates the repository's not-found exception.
     */
    public function testGetDataThrowsWhenCategoryDoesNotExist(): void
    {
        $this->categoryRepositoryMock->expects($this->once())->method('get')
            ->willThrowException(NoSuchEntityException::singleField('id', 5));

        $this->categoryTreeMock->expects($this->never())->method('getTreeCollection');
        $this->extractDataFromCategoryTreeMock->expects($this->never())->method('buildTree');

        $this->expectException(NoSuchEntityException::class);

        $this->model->getData('category', 5, $this->resolveInfoMock, 1);
    }

    /**
     * An enabled category is still resolved as before.
     */
    public function testGetDataReturnsTreeForEnabledCategory(): void
    {
        $categoryMock = $this->createStub(Category::class);
        $categoryMock->method('getIsActive')->willReturn(true);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with(5, 1)->willReturn($categoryMock);

        $collectionMock = $this->createStub(Collection::class);
        $collectionMock->method('count')->willReturn(1);
        $collectionMock->method('getItems')->willReturn([]);
        $this->categoryTreeMock->expects($this->once())->method('getTreeCollection')
            ->with($this->resolveInfoMock, 5, 1)
            ->willReturn($collectionMock);

        $this->extractDataFromCategoryTreeMock->expects($this->once())->method('buildTree')
            ->with($collectionMock, [5])
            ->willReturn([5 => ['name' => 'Test Category']]);

        $result = $this->model->getData('category', 5, $this->resolveInfoMock, 1);

        $this->assertSame('category', $result['type_id']);
        $this->assertSame('Test Category', $result['name']);
    }
}
