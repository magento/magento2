<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
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
     * @dataProvider productProvider
     * @param array $products
     */
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
    public function productProvider()
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
            ->setMethods(['create'])
            ->disableOriginalConstructor()
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
            ->setMethods(['create'])
            ->disableOriginalConstructor()
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
        $configReaderMock = $this->getMockForAbstractClass(ConfigReaderInterface::class);
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
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $sitemapCmsPageMock->expects($this->any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
