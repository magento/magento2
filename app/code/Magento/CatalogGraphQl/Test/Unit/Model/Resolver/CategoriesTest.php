<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver;

use ArrayIterator;
use GraphQL\Language\AST\FieldNode;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Category\Hydrator as CategoryHydrator;
use Magento\CatalogGraphQl\Model\Resolver\Categories;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductCategories;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlattener;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoriesTest extends TestCase
{
    private const STORE_ID = 1;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var AttributesJoiner|MockObject
     */
    private AttributesJoiner $attributesJoiner;

    /**
     * @var CategoryHydrator
     */
    private CategoryHydrator $categoryHydrator;

    /**
     * @var ProductCategories|MockObject
     */
    private ProductCategories $productCategories;

    /**
     * @var Categories
     */
    private Categories $categoriesResolver;

    /**
     * @var ResolveInfo
     */
    private ResolveInfo $info;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->attributesJoiner = $this->createMock(AttributesJoiner::class);
        $this->categoryHydrator = $this->createStub(CategoryHydrator::class);
        $this->productCategories = $this->createMock(ProductCategories::class);

        $valueFactory = $this->createStub(ValueFactory::class);
        $valueFactory->method('create')->willReturnCallback(fn (callable $callback) => $callback);
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(self::STORE_ID);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $this->categoriesResolver = new Categories(
            $this->collectionFactory,
            $this->attributesJoiner,
            $this->createStub(CustomAttributesFlattener::class),
            $valueFactory,
            $this->categoryHydrator,
            $this->productCategories,
            $storeManager
        );

        $this->info = $this->createStub(ResolveInfo::class);
        $this->info->path = ['products', 'items', 0, 'categories'];
        // phpcs:ignore Magento2.Legacy.RestrictedCode.ArrayObjectIsRestricted
        $this->info->fieldNodes = new \ArrayObject([$this->createStub(FieldNode::class)]);
        $this->attributesJoiner->expects($this->atLeastOnce())->method('getQueryFields')->willReturn(['id', 'name']);
        $this->categoryHydrator->method('hydrateCategory')->willReturnCallback(
            fn (Category $category) => ['id' => $category->getId(), 'name' => 'Category ' . $category->getId()]
        );
    }

    public function testCategoriesOfAllResolvedProductsAreLoadedWithOneIndexQueryAndOneCollection(): void
    {
        $this->productCategories->expects($this->never())->method('getCategoryIdsByProduct');
        $this->productCategories->expects($this->once())
            ->method('getCategoryIdsByProducts')
            ->with([10, 20, 30], self::STORE_ID)
            ->willReturn([10 => ['3', '4'], 20 => ['4']]);
        $collection = $this->createCollection(['3', '4'], [3, 4]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->attributesJoiner->expects($this->once())->method('join');

        $deferred = [];
        foreach ([10, 20, 30] as $productId) {
            $deferred[$productId] = $this->resolve($productId, Visibility::VISIBILITY_BOTH);
        }

        $this->assertSame([3, 4], array_keys($deferred[10]()));
        $this->assertSame([4], array_keys($deferred[20]()));
        $this->assertSame([], $deferred[30]());
    }

    public function testCategoriesAreLoadedAgainOnlyForProductsResolvedAfterTheFirstBatch(): void
    {
        $this->productCategories->expects($this->exactly(2))
            ->method('getCategoryIdsByProducts')
            ->willReturnMap([
                [[10], self::STORE_ID, [10 => ['3']]],
                [[20], self::STORE_ID, [20 => ['3', '5']]],
            ]);
        $this->collectionFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $this->createCollection(['3'], [3]),
                $this->createCollection(['5'], [5])
            );

        $first = $this->resolve(10, Visibility::VISIBILITY_BOTH);
        $this->assertSame([3], array_keys($first()));

        $second = $this->resolve(20, Visibility::VISIBILITY_BOTH);
        $this->assertSame([3, 5], array_keys($second()));
    }

    public function testHiddenProductInOrderUsesAssignedCategoryIds(): void
    {
        $this->info->path = ['customer', 'orders', 'items', 0, 'product', 'categories'];
        $this->productCategories->expects($this->never())->method('getCategoryIdsByProducts');
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->createCollection(['7'], [7]));

        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getVisibility')->willReturn(Visibility::VISIBILITY_NOT_VISIBLE);
        $product->expects($this->once())->method('getCategoryIds')->willReturn(['7']);
        $product->expects($this->once())->method('getId')->willReturn(10);

        $deferred = $this->categoriesResolver->resolve(
            $this->createStub(Field::class),
            $this->createStub(ContextInterface::class),
            $this->info,
            ['model' => $product]
        );

        $this->assertSame([7], array_keys($deferred()));
    }

    /**
     * @param int $productId
     * @param int $visibility
     * @return callable
     */
    private function resolve(int $productId, int $visibility): callable
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn($productId);
        $product->method('getVisibility')->willReturn($visibility);

        return $this->categoriesResolver->resolve(
            $this->createStub(Field::class),
            $this->createStub(ContextInterface::class),
            $this->info,
            ['model' => $product]
        );
    }

    /**
     * @param string[] $expectedIds
     * @param int[] $returnedIds
     * @return Collection
     */
    private function createCollection(array $expectedIds, array $returnedIds): Collection
    {
        $items = [];
        foreach ($returnedIds as $id) {
            $category = $this->createStub(Category::class);
            $category->method('getId')->willReturn($id);
            $items[] = $category;
        }
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('addIdFilter')
            ->with($this->callback(fn (array $ids) => array_values($ids) == $expectedIds))
            ->willReturnSelf();
        $collection->expects($this->once())->method('getIterator')->willReturn(new ArrayIterator($items));

        return $collection;
    }
}
