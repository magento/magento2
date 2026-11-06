<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ItemProvider\Product as ProductItemResolver;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product as ProductResource;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testGetItemData(): void
    {
        $entity = new DataObject([
            'url' => 'entity.html',
            'updated_at' => '2026-01-01',
            'images' => new DataObject(['title' => 'Image & caption']),
        ]);
        $resolver = new ProductItemResolver(
            $this->getConfigReaderMock(),
            $this->getProductFactoryMock($this->getProductCollectionMock([])),
            $this->getItemFactoryMock()
        );

        self::assertSame([
            'url' => 'entity.html',
            'updatedAt' => '2026-01-01',
            'images' => $entity->getImages(),
            'priority' => '1.0',
            'changeFrequency' => 'daily',
        ], $resolver->getItemData($entity, 1));
    }

    public function testGetItemsUsesOverriddenItemData(): void
    {
        $entities = [
            4 => new DataObject(['url' => 'custom.html']),
            9 => new DataObject(['url' => 'default.html']),
        ];
        $resolver = new class (
            $this->getConfigReaderMock(),
            $this->getProductFactoryMock($this->getProductCollectionMock($entities)),
            $this->getItemFactoryMock()
        ) extends ProductItemResolver {
            public function getItemData($item, $storeId)
            {
                $data = parent::getItemData($item, $storeId);
                $priorities = ['custom.html' => '0.3', 'default.html' => $data['priority']];
                $data['priority'] = $priorities[$item->getUrl()];
                return $data;
            }
        };

        $items = $resolver->getItems(1);

        self::assertSame([4, 9], array_keys($items));
        self::assertSame('0.3', $items[4]->getPriority());
        self::assertSame('1.0', $items[9]->getPriority());
        self::assertSame('custom.html', $items[4]->getUrl());
        self::assertSame('daily', $items[4]->getChangeFrequency());
    }

    public function testGetItemsEmpty()
    {
        $configReaderMock = $this->getConfigReaderMock();
        $productMock = $this->getProductCollectionMock([]);
        $productFactoryMock = $this->getProductFactoryMock($productMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new ProductItemResolver($configReaderMock, $productFactoryMock, $itemFactoryMock);

        self::assertSame([], $resolver->getItems(1));
    }

    /**
     * @param array $products
     */
    #[DataProvider('productProvider')]
    public function testGetItems(array $products)
    {
        $configReaderMock = $this->getConfigReaderMock();
        $productMock = $this->getProductCollectionMock($products);
        $productFactoryMock = $this->getProductFactoryMock($productMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new ProductItemResolver($configReaderMock, $productFactoryMock, $itemFactoryMock);
        $items = $resolver->getItems(1);

        self::assertCount(count($products), $items);
        foreach ($products as $index => $product) {
            self::assertSame($product->getUpdatedAt(), $items[$index]->getUpdatedAt());
            self::assertSame('daily', $items[$index]->getChangeFrequency());
            self::assertSame('1.0', $items[$index]->getPriority());
            self::assertSame($product->getImages(), $items[$index]->getImages());
            self::assertSame($product->getUrl(), $items[$index]->getUrl());
        }
    }

    /**
     * @return array
     */
    public static function productProvider()
    {
        $storeBaseMediaUrl = 'http://store.com/media/catalog/product/cache/c9e0b0ef589f3508e5ba515cde53c5ff/';
        return [
            [
                [
                    new DataObject(
                        ['url' => 'product.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                    new DataObject(
                        [
                            'url' => 'product2.html',
                            'updated_at' => '2012-12-21 00:00:00',
                            'images' => new DataObject(
                                [
                                    'collection' => [
                                        new DataObject(
                                            [
                                                'url' => $storeBaseMediaUrl . 'i/m/image1.png',
                                                'caption' => 'caption & > title < "'
                                            ]
                                        ),
                                        new DataObject(
                                            [
                                                'url' => $storeBaseMediaUrl . 'i/m/image_no_caption.png',
                                                'caption' => null
                                            ]
                                        ),
                                    ],
                                    'thumbnail' => $storeBaseMediaUrl . 't/h/thumbnail.jpg',
                                    'title' => 'Product & > title < "',
                                ]
                            ),
                        ]
                    ),
                ]
            ]
        ];
    }

    /**
     * @param $returnValue
     * @return MockObject
     */
    private function getProductFactoryMock($returnValue)
    {
        $cmsPageFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $cmsPageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($returnValue);

        return $cmsPageFactoryMock;
    }

    /**
     * @return MockObject
     */
    private function getItemFactoryMock()
    {
        $itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $helper = new ObjectManager($this);

                return $helper->getObject(SitemapItem::class, $data);
            });

        return $itemFactoryMock;
    }

    /**
     * @return MockObject
     */
    private function getConfigReaderMock()
    {
        $configReaderMock = $this->createMock(ConfigReaderInterface::class);
        $configReaderMock->expects($this->any())
            ->method('getPriority')
            ->willReturn('1.0');
        $configReaderMock->expects($this->any())
            ->method('getChangeFrequency')
            ->willReturn('daily');

        return $configReaderMock;
    }

    /**
     * @param $returnValue
     * @return MockObject
     */
    private function getProductCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollection'])
            ->getMock();

        $sitemapCmsPageMock->expects($this->any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
