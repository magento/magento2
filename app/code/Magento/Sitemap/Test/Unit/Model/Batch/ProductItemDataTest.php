<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\Batch;

use Magento\Framework\DataObject;
use Magento\Sitemap\Model\Batch\Sitemap;
use Magento\Sitemap\Model\ItemProvider\ProductConfigReader;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\Product;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\ProductFactory;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class ProductItemDataTest extends TestCase
{
    public function testGetItemData(): void
    {
        $sitemap = $this->getMockBuilder(Sitemap::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $config = $this->createMock(ProductConfigReader::class);
        $config->expects(self::once())->method('getPriority')->with(2)->willReturn('0.5');
        $config->expects(self::once())->method('getChangeFrequency')->with(2)->willReturn('daily');
        (new ReflectionProperty(Sitemap::class, 'productConfigReader'))->setValue($sitemap, $config);
        $images = new DataObject(['title' => 'Image & caption']);
        $product = new DataObject(['url' => 'product.html', 'updated_at' => '2026-01-01', 'images' => $images]);

        self::assertSame([
            'url' => 'product.html',
            'updatedAt' => '2026-01-01',
            'images' => $images,
            'priority' => '0.5',
            'changeFrequency' => 'daily',
        ], $sitemap->buildProductItemData($product, 2));
    }

    #[DataProvider('streamingProvider')]
    public function testStreamProductsUsesItemData(bool $override, string $priority): void
    {
        $methods = ['_getSitemapRow', '_isSplitRequired', '_writeSitemapRow'];
        if ($override) {
            $methods[] = 'buildProductItemData';
        }
        $sitemap = $this->getMockBuilder(Sitemap::class)
            ->disableOriginalConstructor()->onlyMethods($methods)->getMock();
        $sitemap->setData('store_id', 2);
        (new ReflectionProperty(Sitemap::class, '_fileSize'))->setValue($sitemap, 1);
        $product = new DataObject(['url' => 'product.html', 'updated_at' => '2026-01-01']);
        $expected = [
            'url' => 'product.html',
            'updatedAt' => '2026-01-01',
            'images' => null,
            'priority' => $priority,
            'changeFrequency' => 'daily',
        ];
        if ($override) {
            $sitemap->expects(self::once())->method('buildProductItemData')->with($product, 2)->willReturn($expected);
        }
        $config = $this->createMock(ProductConfigReader::class);
        $config->method('getPriority')->with(2)->willReturn('0.5');
        $config->method('getChangeFrequency')->with(2)->willReturn('daily');
        (new ReflectionProperty(Sitemap::class, 'productConfigReader'))->setValue($sitemap, $config);
        $resource = $this->createMock(Product::class);
        $resource->method('getCollection')->with(2)->willReturnCallback(static function () use ($product) {
            yield $product;
        });
        $resourceFactory = $this->createMock(ProductFactory::class);
        $resourceFactory->method('create')->willReturn($resource);
        (new ReflectionProperty(Sitemap::class, 'batchProductFactory'))->setValue($sitemap, $resourceFactory);
        $factory = $this->createMock(SitemapItemInterfaceFactory::class);
        $factory->expects(self::once())->method('create')->with($expected)->willReturn(new SitemapItem(...$expected));
        (new ReflectionProperty(Sitemap::class, 'sitemapItemFactory'))->setValue($sitemap, $factory);
        $sitemap->expects(self::once())->method('_getSitemapRow')
            ->with('product.html', '2026-01-01', 'daily', $priority, null)->willReturn('<url/>');
        $sitemap->method('_isSplitRequired')->willReturn(false);
        $sitemap->expects(self::once())->method('_writeSitemapRow')->with('<url/>');

        (new ReflectionMethod(Sitemap::class, 'streamProducts'))->invoke($sitemap);
    }

    public static function streamingProvider(): array
    {
        return [
            'unchanged factory arguments' => [false, '0.5'],
            'overridden item data' => [true, '0.3'],
        ];
    }
}
