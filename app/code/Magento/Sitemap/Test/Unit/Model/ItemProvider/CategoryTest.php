<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testGetItemData(): void
    {
        $entity = new DataObject([
            'url' => 'entity.html',
            'updated_at' => '2026-01-01',
            'images' => new DataObject(['title' => 'Image & caption']),
        ]);
        $resolver = new CategoryItemResolver(
            $this->getConfigReaderMock(),
            $this->getCategoryFactoryMock($this->getCategoryCollectionMock([])),
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
            $this->getCategoryFactoryMock($this->getCategoryCollectionMock($entities)),
            $this->getItemFactoryMock()
        ) extends CategoryItemResolver {
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
        $categoryMock = $this->getCategoryCollectionMock([]);
        $categoryFactoryMock = $this->getCategoryFactoryMock($categoryMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CategoryItemResolver($configReaderMock, $categoryFactoryMock, $itemFactoryMock);

        $this->assertSame([], $resolver->getItems(1));
    }

    /**
     * @param array $categories
     */
    #[DataProvider('categoryProvider')]
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
    public static function categoryProvider()
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
    private function getCategoryCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(CategoryResource::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollection'])
            ->getMock();

        $sitemapCmsPageMock->expects($this->any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
