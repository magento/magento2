<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\CategorySitemapItemResolver;
use Magento\Sitemap\Model\ResourceModel\Catalog\Category;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;

class CategorySitemapItemResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetItemsEmpty()
    {
        $storeConfigMock = $this->getStoreConfigMock([
            CategorySitemapItemResolver::XML_PATH_CATEGORY_CHANGEFREQ => 'daily',
            CategorySitemapItemResolver::XML_PATH_CATEGORY_PRIORITY => '1.0',
        ]);

        $categoryMock = $this->getCategoryCollectionMock([]);
        $cmsPageFactoryMock = $this->getCategoryFactoryMock($categoryMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CategorySitemapItemResolver($storeConfigMock, $cmsPageFactoryMock, $itemFactoryMock);
        $this->assertSame([], $resolver->getItems(1));
    }

    /**
     * @dataProvider categoryProvider
     * @param array $categories
     */
    public function testGetItems(array $categories)
    {
        $storeConfigMock = $this->getStoreConfigMock([
            CategorySitemapItemResolver::XML_PATH_CATEGORY_CHANGEFREQ => 'daily',
            CategorySitemapItemResolver::XML_PATH_CATEGORY_PRIORITY => '1.0',
        ]);

        $categoryMock = $this->getCategoryCollectionMock($categories);

        $cmsPageFactoryMock = $this->getCategoryFactoryMock($categoryMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CategorySitemapItemResolver($storeConfigMock, $cmsPageFactoryMock, $itemFactoryMock);
        $items = $resolver->getItems(1);
        $this->assertTrue(count($items) == count($categories));
        foreach ($categories as $index => $category) {
            $this->assertSame($category->getUpdatedAt(), $items[$index]->getUpdatedAt());
            $this->assertSame('daily', $items[$index]->getChangeFrequency());
            $this->assertSame('1.0', $items[$index]->getPriority());
            $this->assertSame($category->getImages(), $items[$index]->getImages());
            $this->assertSame($category->getUrl(), $items[$index]->getUrl());
        }
    }

    /**
     * @return array
     */
    public function categoryProvider()
    {
        return [
            [
                [
                    new \Magento\Framework\DataObject(
                        ['url' => 'category.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                    new \Magento\Framework\DataObject(
                        ['url' => '/category/sub-category.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                ]
            ]
        ];
    }

    /**
     * @param $returnValue
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCategoryFactoryMock($returnValue)
    {
        $cmsPageFactoryMock = $this->getMockBuilder(CategoryFactory::class)
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
    private function getCategoryCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(Category::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $sitemapCmsPageMock->expects(self::any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
