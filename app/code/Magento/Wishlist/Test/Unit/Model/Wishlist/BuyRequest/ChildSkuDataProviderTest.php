<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model\Wishlist\BuyRequest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Wishlist\BuyRequest\ChildSkuDataProvider;
use Magento\Wishlist\Model\Wishlist\Data\SelectedOption;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChildSkuDataProviderTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Configurable|MockObject
     */
    private Configurable $configurableType;

    /**
     * @var ChildSkuDataProvider
     */
    private ChildSkuDataProvider $provider;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->configurableType = $this->createMock(Configurable::class);

        $this->provider = new ChildSkuDataProvider(
            $this->productRepository,
            $this->configurableType
        );
    }

    public function testExecuteReturnsEmptyArrayWhenNoParentSku(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', null);

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayWhenParentSkuEqualsSku(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'same-sku', 'same-sku');

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayWhenSelectedOptionsPresent(): void
    {
        $selectedOption = $this->createMock(SelectedOption::class);
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku', null, null, [$selectedOption]);

        $this->productRepository->expects($this->never())->method('get');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayOnNoSuchEntityException(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        $this->productRepository->expects($this->once())
            ->method('get')
            ->willThrowException(new NoSuchEntityException());

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsEmptyArrayWhenParentIsNotConfigurable(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        /** @var Product|MockObject $childProduct */
        $childProduct = $this->createMock(Product::class);
        /** @var Product|MockObject $parentProduct */
        $parentProduct = $this->createMock(Product::class);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $parentProduct->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->configurableType->expects($this->never())->method('getConfigurableAttributesAsArray');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }

    public function testExecuteReturnsSuperAttributeMapForValidChildSku(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');
        $productId = 42;

        /** @var Product|MockObject $childProduct */
        $childProduct = $this->createMock(Product::class);
        /** @var Product|MockObject $parentProduct */
        $parentProduct = $this->createMock(Product::class);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $parentProduct->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableType->expects($this->once())
            ->method('getConfigurableAttributesAsArray')
            ->with($parentProduct)
            ->willReturn([
                ['attribute_id' => '93', 'attribute_code' => 'color'],
                ['attribute_id' => '157', 'attribute_code' => 'size'],
            ]);

        $childProduct->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'color' => '56',
                    'size'  => '168',
                    default => null,
                };
            });

        $result = $this->provider->execute($wishlistItem, $productId);

        $this->assertSame(
            [
                'super_attribute' => [93 => '56', 157 => '168'],
                'product' => $productId,
            ],
            $result
        );
    }

    public function testExecuteReturnsSuperAttributeMapWithoutProductIdWhenProductIdIsNull(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        /** @var Product|MockObject $childProduct */
        $childProduct = $this->createMock(Product::class);
        /** @var Product|MockObject $parentProduct */
        $parentProduct = $this->createMock(Product::class);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $parentProduct->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableType->expects($this->once())
            ->method('getConfigurableAttributesAsArray')
            ->with($parentProduct)
            ->willReturn([
                ['attribute_id' => '93', 'attribute_code' => 'color'],
            ]);

        $childProduct->expects($this->once())
            ->method('getData')
            ->with('color')
            ->willReturn('56');

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertArrayHasKey('super_attribute', $result);
        $this->assertArrayNotHasKey('product', $result);
        $this->assertSame([93 => '56'], $result['super_attribute']);
    }

    public function testExecuteSkipsAttributesWithEmptyValueOnChild(): void
    {
        $wishlistItem = new WishlistItem(1.0, 'child-sku', 'parent-sku');

        /** @var Product|MockObject $childProduct */
        $childProduct = $this->createMock(Product::class);
        /** @var Product|MockObject $parentProduct */
        $parentProduct = $this->createMock(Product::class);

        $this->productRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['child-sku', false, null, true, $childProduct],
                ['parent-sku', false, null, true, $parentProduct],
            ]);

        $parentProduct->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->configurableType->expects($this->once())
            ->method('getConfigurableAttributesAsArray')
            ->with($parentProduct)
            ->willReturn([
                ['attribute_id' => '93', 'attribute_code' => 'color'],
                ['attribute_id' => '157', 'attribute_code' => 'size'],
            ]);

        $childProduct->expects($this->exactly(2))
            ->method('getData')
            ->willReturnCallback(static function (string $key) {
                return match ($key) {
                    'color' => null,
                    'size'  => '',
                    default => null,
                };
            });

        $result = $this->provider->execute($wishlistItem, null);

        $this->assertSame([], $result);
    }
}
