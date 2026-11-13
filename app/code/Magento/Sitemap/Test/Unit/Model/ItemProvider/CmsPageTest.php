<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\ItemProvider;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ItemProvider\CmsPage as CmsPageItemResolver;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ResourceModel\Cms\Page as CmsPageResource;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CmsPageTest extends TestCase
{
    public function testGetItemData(): void
    {
        $entity = new DataObject([
            'url' => 'entity.html',
            'updated_at' => '2026-01-01',
            'images' => new DataObject(['title' => 'Image & caption']),
        ]);
        $resolver = new CmsPageItemResolver(
            $this->getConfigReaderMock(),
            $this->getCmsPageFactoryMock($this->getCmsPageCollectionMock([])),
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
            $this->getCmsPageFactoryMock($this->getCmsPageCollectionMock($entities)),
            $this->getItemFactoryMock()
        ) extends CmsPageItemResolver {
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
        $cmsPageMock = $this->getCmsPageCollectionMock([]);
        $cmsPageFactoryMock = $this->getCmsPageFactoryMock($cmsPageMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CmsPageItemResolver($configReaderMock, $cmsPageFactoryMock, $itemFactoryMock);

        $this->assertSame([], $resolver->getItems(1));
    }

    /**
     * @param array $pages
     */
    #[DataProvider('pageProvider')]
    public function testGetItems(array $pages = [])
    {
        $configReaderMock = $this->getConfigReaderMock();
        $cmsPageMock = $this->getCmsPageCollectionMock($pages);
        $cmsPageFactoryMock = $this->getCmsPageFactoryMock($cmsPageMock);
        $itemFactoryMock = $this->getItemFactoryMock();

        $resolver = new CmsPageItemResolver($configReaderMock, $cmsPageFactoryMock, $itemFactoryMock);
        $items = $resolver->getItems(1);

        $this->assertCount(count($pages), $items);
        foreach ($pages as $index => $page) {
            $this->assertSame($page->getUpdatedAt(), $items[$index]->getUpdatedAt());
            $this->assertSame('daily', $items[$index]->getChangeFrequency());
            $this->assertSame('1.0', $items[$index]->getPriority());
            $this->assertSame($page->getImages(), $items[$index]->getImages());
            $this->assertSame($page->getUrl(), $items[$index]->getUrl());
        }
    }

    /**
     * @return array
     */
    public static function pageProvider()
    {
        return [
            [
                [
                    new DataObject([
                        'url' => 'http://dummy.url',
                        'id' => '/url',
                        'updated_at' => '2017-01-01 23:59:59'
                    ])
                ]
            ]
        ];
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

        $itemFactoryMock->expects(self::any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $helper = new ObjectManager($this);

                return $helper->getObject(SitemapItem::class, $data);
            });

        return $itemFactoryMock;
    }

    /**
     * @param $returnValue
     * @return MockObject
     */
    private function getCmsPageFactoryMock($returnValue)
    {
        $cmsPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $cmsPageFactoryMock->expects(self::any())
            ->method('create')
            ->willReturn($returnValue);

        return $cmsPageFactoryMock;
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
    private function getCmsPageCollectionMock($returnValue)
    {
        $sitemapCmsPageMock = $this->getMockBuilder(CmsPageResource::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollection'])
            ->getMock();

        $sitemapCmsPageMock->expects(self::any())
            ->method('getCollection')
            ->willReturn($returnValue);

        return $sitemapCmsPageMock;
    }
}
