<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ItemProvider\Category as CategoryItemResolver;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Category as CategoryResource;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testGetItemsEmpty()
    {
        $configReaderMock = $this->getConfigReaderMock();
        $categoryMock = $this->getCategoryCollectionMock([]);
        $categoryFactoryMock = $this->getCategoryFactoryMock($categoryMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CategoryItemResolver($configReaderMock, $categoryFactoryMock, $itemFactoryMock);

        $this->assertSame([], $resolver->getItems(1));
    }

    /**
     * @dataProvider categoryProvider
     * @param array $categories
     */
    public function testGetItems(array $categories)
    {
        $configReaderMock = $this->getConfigReaderMock();
        $categoryMock = $this->getCategoryCollectionMock($categories);
        $categoryFactoryMock = $this->getCategoryFactoryMock($categoryMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CategoryItemResolver($configReaderMock, $categoryFactoryMock, $itemFactoryMock);
        $items = $resolver->getItems(1);

        $this->assertCount(count($categories), $items);
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
                    new DataObject(
                        ['url' => 'category.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                    new DataObject(
                        ['url' => '/category/sub-category.html', 'updated_at' => '2012-12-21 00:00:00']
                    ),
                ]
            ]
        ];
    }

    /**
     * @param $returnValue
     * @return MockObject
     */
    private function getCategoryFactoryMock($returnValue)
    {
        $cmsPageFactoryMock = $this->getMockBuilder(CategoryFactory::class)
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
    private function getCategoryCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(CategoryResource::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $sitemapCmsPageMock->expects($this->any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
