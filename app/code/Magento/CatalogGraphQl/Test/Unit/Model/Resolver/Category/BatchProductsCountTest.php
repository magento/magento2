<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogGraphQl\Model\Category\ProductsCountProvider;
use Magento\CatalogGraphQl\Model\Resolver\Category\BatchProductsCount;
use Magento\CatalogGraphQl\Model\Resolver\Category\ProductsCount;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see BatchProductsCount
 */
class BatchProductsCountTest extends TestCase
{
    /**
     * @var ProductsCountProvider|MockObject
     */
    private ProductsCountProvider $productsCountProvider;

    /**
     * @var ProductsCount|MockObject
     */
    private ProductsCount $productsCount;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface $context;

    /**
     * @var Field|MockObject
     */
    private Field $field;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var BatchProductsCount
     */
    private BatchProductsCount $resolver;

    protected function setUp(): void
    {
        $this->productsCountProvider = $this->createMock(ProductsCountProvider::class);
        $this->productsCount = $this->createMock(ProductsCount::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->field = $this->createMock(Field::class);
        $this->objectManager = new ObjectManager($this);

        $this->resolver = new BatchProductsCount($this->productsCountProvider, $this->productsCount);
    }

    public function testCategoriesOfTwoStoresAreCountedWithOneQueryPerStore(): void
    {
        $requests = [
            $this->createRequest(3, 1, true),
            $this->createRequest(4, 1, true),
            $this->createRequest(3, 2, true),
        ];
        $calls = [];
        $this->productsCountProvider->expects($this->exactly(2))
            ->method('getProductsCounts')
            ->willReturnCallback(
                function (int $storeId, array $categoryIds, bool $isAnchor) use (&$calls): array {
                    $calls[] = [$storeId, $categoryIds, $isAnchor];
                    return $storeId === 1 ? [3 => 7, 4 => 2] : [3 => 5];
                }
            );

        $response = $this->resolver->resolve($this->context, $this->field, $requests);

        $this->assertSame([[1, [3, 4], true], [2, [3], true]], $calls);
        $this->assertSame(7, $response->findResponseFor($requests[0]));
        $this->assertSame(2, $response->findResponseFor($requests[1]));
        $this->assertSame(5, $response->findResponseFor($requests[2]));
    }

    public function testAnchorAndNonAnchorCategoriesAreCountedSeparately(): void
    {
        $requests = [
            $this->createRequest(3, 1, true),
            $this->createRequest(4, 1, false),
        ];
        $calls = [];
        $this->productsCountProvider->expects($this->exactly(2))
            ->method('getProductsCounts')
            ->willReturnCallback(
                function (int $storeId, array $categoryIds, bool $isAnchor) use (&$calls): array {
                    $calls[] = [$storeId, $categoryIds, $isAnchor];
                    return $isAnchor ? [3 => 9] : [4 => 1];
                }
            );

        $response = $this->resolver->resolve($this->context, $this->field, $requests);

        $this->assertSame([[1, [3], true], [1, [4], false]], $calls);
        $this->assertSame(9, $response->findResponseFor($requests[0]));
        $this->assertSame(1, $response->findResponseFor($requests[1]));
    }

    public function testCategoryWithoutIndexRowsResolvesToZero(): void
    {
        $request = $this->createRequest(11, 1, true);
        $this->productsCountProvider->method('getProductsCounts')->willReturn([]);

        $response = $this->resolver->resolve($this->context, $this->field, [$request]);

        $this->assertSame(0, $response->findResponseFor($request));
    }

    public function testAdminStoreFallsBackToCollectionResolver(): void
    {
        $request = $this->createRequest(3, 0, true);
        $this->productsCountProvider->expects($this->never())->method('getProductsCounts');
        $this->productsCount->expects($this->once())->method('resolve')->willReturn(42);

        $response = $this->resolver->resolve($this->context, $this->field, [$request]);

        $this->assertSame(42, $response->findResponseFor($request));
    }

    public function testMissingCategoryModelIsRejected(): void
    {
        $request = $this->createMock(BatchRequestItemInterface::class);
        $request->method('getValue')->willReturn([]);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('"model" value should be specified');

        $this->resolver->resolve($this->context, $this->field, [$request]);
    }

    /**
     * @param int $categoryId
     * @param int $storeId
     * @param bool $isAnchor
     * @return BatchRequestItemInterface|MockObject
     */
    private function createRequest(int $categoryId, int $storeId, bool $isAnchor): BatchRequestItemInterface
    {
        /** @var Category $category */
        $category = $this->objectManager->getObject(Category::class);
        $category->setData(
            [
                'id' => $categoryId,
                'entity_id' => $categoryId,
                'store_id' => $storeId,
                'is_anchor' => $isAnchor ? 1 : 0,
            ]
        );

        $request = $this->createMock(BatchRequestItemInterface::class);
        $request->method('getValue')->willReturn(['model' => $category]);
        $request->method('getArgs')->willReturn([]);
        $request->method('getInfo')->willReturn($this->createMock(ResolveInfo::class));

        return $request;
    }
}
