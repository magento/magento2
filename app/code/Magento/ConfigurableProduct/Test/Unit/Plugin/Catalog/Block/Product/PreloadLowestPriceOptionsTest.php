<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Plugin\Catalog\Block\Product\PreloadLowestPriceOptions;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use PHPUnit\Framework\TestCase;

class PreloadLowestPriceOptionsTest extends TestCase
{
    public function testRegistersLoadedCollectionOnce(): void
    {
        $first = $this->product(1, Configurable::TYPE_CODE);
        $second = $this->product(2, Configurable::TYPE_CODE);
        $items = [1 => $first, 2 => $second];
        $collection = $this->collection(true, $items);
        $block = $this->createStub(ListProduct::class);
        $provider = $this->createMock(LowestPriceOptionsProvider::class);
        $provider->expects($this->once())->method('preloadProducts')->with($items);
        $plugin = new PreloadLowestPriceOptions($provider);

        $this->assertSame($collection, $plugin->afterGetLoadedProductCollection($block, $collection));
        $this->assertNull($plugin->beforeGetProductPrice($block, $first));
        $this->assertNull($plugin->beforeGetProductPrice($block, $second));
    }

    public function testSkipsCollectionThatIsNotLoaded(): void
    {
        $product = $this->product(1, Configurable::TYPE_CODE);
        $this->assertNotRegistered($this->collection(false, [1 => $product]), $product);
    }

    public function testSkipsProductOutsideTheCollection(): void
    {
        $this->assertNotRegistered(
            $this->collection(true, [1 => $this->product(1, Configurable::TYPE_CODE)]),
            $this->product(2, Configurable::TYPE_CODE)
        );
    }

    public function testSkipsNonConfigurableProduct(): void
    {
        $product = $this->product(1, 'simple');
        $this->assertNotRegistered($this->collection(true, [1 => $product]), $product);
    }

    public function testDoesNotInitializeTheBlockCollection(): void
    {
        $block = $this->createMock(ListProduct::class);
        $block->expects($this->never())->method('getLoadedProductCollection');
        $provider = $this->createMock(LowestPriceOptionsProvider::class);
        $provider->expects($this->never())->method('preloadProducts');

        (new PreloadLowestPriceOptions($provider))
            ->beforeGetProductPrice($block, $this->product(1, Configurable::TYPE_CODE));
    }

    public function testSkipsCustomProviderImplementation(): void
    {
        $product = $this->product(1, Configurable::TYPE_CODE);
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->never())->method('getItems');
        $block = $this->createStub(ListProduct::class);
        $plugin = new PreloadLowestPriceOptions($this->createStub(LowestPriceOptionsProviderInterface::class));

        $plugin->afterGetLoadedProductCollection($block, $collection);
        $plugin->beforeGetProductPrice($block, $product);
    }

    private function assertNotRegistered(Collection $collection, Product $product): void
    {
        $block = $this->createStub(ListProduct::class);
        $provider = $this->createMock(LowestPriceOptionsProvider::class);
        $provider->expects($this->never())->method('preloadProducts');
        $plugin = new PreloadLowestPriceOptions($provider);

        $plugin->afterGetLoadedProductCollection($block, $collection);
        $this->assertNull($plugin->beforeGetProductPrice($block, $product));
    }

    private function collection(bool $isLoaded, array $items): Collection
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('isLoaded')->willReturn($isLoaded);
        $collection->method('getItems')->willReturn($items);
        $collection->method('getItemById')->willReturnCallback(fn ($id) => $items[$id] ?? null);

        return $collection;
    }

    private function product(int $id, string $typeId): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn($id);
        $product->method('getTypeId')->willReturn($typeId);

        return $product;
    }
}
