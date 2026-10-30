<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider\Batch;

use Magento\Framework\DataObject;
use Magento\Sitemap\Model\ItemProvider\Batch\Product;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\Product as ProductResource;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\ProductFactory;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\TestCase;

class ProductItemDataTest extends TestCase
{
    public function testGetItemData(): void
    {
        $config = $this->createMock(ConfigReaderInterface::class);
        $config->expects(self::once())->method('getPriority')->with(2)->willReturn('0.5');
        $config->expects(self::once())->method('getChangeFrequency')->with(2)->willReturn('daily');
        $factory = $this->createMock(SitemapItemInterfaceFactory::class);
        $factory->expects(self::never())->method('create');
        $provider = new Product($config, $this->createMock(ProductFactory::class), $factory);
        $images = new DataObject(['title' => 'Image & caption']);
        $entity = new DataObject(['url' => 'product.html', 'updated_at' => '2026-01-01', 'images' => $images]);

        self::assertSame([
            'url' => 'product.html',
            'updatedAt' => '2026-01-01',
            'images' => $images,
            'priority' => '0.5',
            'changeFrequency' => 'daily',
        ], $provider->getItemData($entity, 2));
    }

    public function testGetItemsUsesOverriddenItemData(): void
    {
        $config = $this->createMock(ConfigReaderInterface::class);
        $config->method('getPriority')->with(2)->willReturn('0.5');
        $config->method('getChangeFrequency')->with(2)->willReturn('daily');
        $resource = $this->createMock(ProductResource::class);
        $resource->method('getCollectionArray')->with(2)->willReturn([
            4 => new DataObject(['url' => 'custom.html']),
            9 => new DataObject(['url' => 'default.html']),
        ]);
        $resourceFactory = $this->createMock(ProductFactory::class);
        $resourceFactory->method('create')->willReturn($resource);
        $itemFactory = $this->createMock(SitemapItemInterfaceFactory::class);
        $itemFactory->expects(self::exactly(2))->method('create')->willReturnCallback(
            static fn (array $data) => new SitemapItem(...$data)
        );
        $provider = new class ($config, $resourceFactory, $itemFactory) extends Product {
            public function getItemData($item, $storeId)
            {
                $data = parent::getItemData($item, $storeId);
                $priorities = ['custom.html' => '0.3', 'default.html' => $data['priority']];
                $data['priority'] = $priorities[$item->getUrl()];
                return $data;
            }
        };

        $items = $provider->getItems(2);

        self::assertSame([4, 9], array_keys($items));
        self::assertSame('0.3', $items[4]->getPriority());
        self::assertSame('0.5', $items[9]->getPriority());
        self::assertSame('custom.html', $items[4]->getUrl());
        self::assertSame('daily', $items[4]->getChangeFrequency());
    }
}
