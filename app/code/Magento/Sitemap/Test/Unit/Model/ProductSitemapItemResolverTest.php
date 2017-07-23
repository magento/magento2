<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ProductSitemapItemResolver;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;

class ProductSitemapItemResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetItemsEmpty()
    {
        $storeConfigMock = $this->getStoreConfigMock([
            ProductSitemapItemResolver::XML_PATH_PRODUCT_CHANGEFREQ => 'daily',
            ProductSitemapItemResolver::XML_PATH_PRODUCT_PRIORITY => '1.0',
        ]);

        $ProductMock = $this->getProductCollectionMock([]);
        $cmsPageFactoryMock = $this->getProductFactoryMock($ProductMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new ProductSitemapItemResolver($storeConfigMock, $cmsPageFactoryMock, $itemFactoryMock);
        self::assertSame([], $resolver->getItems(1));
    }

    /**
     * @dataProvider productProvider
     * @param array $products
     */
    public function testGetItems(array $products)
    {
        $storeConfigMock = $this->getStoreConfigMock([
            ProductSitemapItemResolver::XML_PATH_PRODUCT_CHANGEFREQ => 'daily',
            ProductSitemapItemResolver::XML_PATH_PRODUCT_PRIORITY => '1.0',
        ]);

        $ProductMock = $this->getProductCollectionMock($products);

        $cmsPageFactoryMock = $this->getProductFactoryMock($ProductMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new ProductSitemapItemResolver($storeConfigMock, $cmsPageFactoryMock, $itemFactoryMock);
        $items = $resolver->getItems(1);
        self::assertTrue(count($items) == count($products));
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
        $storeBaseMediaUrl = 'http://store.com/pub/media/catalog/product/cache/c9e0b0ef589f3508e5ba515cde53c5ff/';
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
                                                'url' => $storeBaseMediaUrl.'i/m/image1.png',
                                                'caption' => 'caption & > title < "'
                                            ]
                                        ),
                                        new DataObject(
                                            ['url' => $storeBaseMediaUrl.'i/m/image_no_caption.png', 'caption' => null]
                                        ),
                                    ],
                                    'thumbnail' => $storeBaseMediaUrl.'t/h/thumbnail.jpg',
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductFactoryMock($returnValue)
    {
        $cmsPageFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $cmsPageFactoryMock->expects(self::any())
            ->method('create')
            ->willReturn($returnValue);

        return $cmsPageFactoryMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getItemFactoryMock()
    {
        $itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemFactoryMock->expects(self::any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $helper = new ObjectManager($this);

                return $helper->getObject(SitemapItem::class, $data);
            });

        return $itemFactoryMock;
    }

    /**
     * @param array $pathMap
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStoreConfigMock(array $pathMap = [])
    {
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfigMock->method('getValue')
            ->willReturnCallback(function ($path) use ($pathMap) {
                return isset($pathMap[$path]) ? $pathMap[$path] : null;
            });

        return $scopeConfigMock;
    }

    /**
     * @param $returnValue
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(Product::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $sitemapCmsPageMock->expects(self::any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
